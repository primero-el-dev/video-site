<?php

namespace App\Notification;

use App\Entity\Comment;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Interfaces\HasOwnerInterface;
use App\Entity\Notification\Notification;
use App\Entity\User;
use App\Entity\UserSubjectAction\UserSubjectAction;
use App\Entity\Video;
use App\Notification\NotificationManagerInterface;
use App\Traits\EntityManagerTrait;

abstract class NotificationManager implements NotificationManagerInterface
{
    use EntityManagerTrait;

    /**
     * {@inheritDoc}
     */
    abstract public function notify(array $users, array $notifications): void;

    /**
     * @return (User|string)[] users or roles
     */
    public function getUsersToNotify(User $user, UserActionEnum $action, EntityInterface $subject): array
    {
        $users = [];

        // Disable in case ofdeleted objects' actions
        $this->em->getFilters()->disable('softdeleteable');

        if (in_array($action, [
            UserActionEnum::LIKE, 
            UserActionEnum::LIKE_DELETE, 
            UserActionEnum::DISLIKE, 
            UserActionEnum::DISLIKE_DELETE, 
            UserActionEnum::REPORT, 
            UserActionEnum::REPORT_DELETE, 
            UserActionEnum::SUBSCRIBE,
            UserActionEnum::SUBSCRIBE_DELETE,
        ])) {
            if ($subject instanceof HasOwnerInterface && $subject->getOwner()->getId() !== $user->getId()) {
                $users[] = $subject->getOwner();
            } elseif ($subject instanceof User && $subject->getId() !== $user->getId()) {
                $users[] = $subject;
            }
        }

        if ($action === UserActionEnum::REPORT) {
            $users[] = 'ROLE_ADMIN';
        }

        if (in_array($action, [
            UserActionEnum::VIDEO_CREATE, 
            UserActionEnum::VIDEO_DELETE, 
            UserActionEnum::PLAYLIST_CREATE, 
            UserActionEnum::PLAYLIST_DELETE,
        ])) {
            $additionalUsers = $subject
                ->getOwner()
                ->getOtherUsersActions()
                ->filter(fn($a) => $a->getAction() === UserActionEnum::SUBSCRIBE)
                ->map(fn($a) => $a->getUser())
                ->toArray();

            $users = array_merge($users, $additionalUsers);
        }

        if (in_array($action, [UserActionEnum::COMMENT_CREATE, UserActionEnum::COMMENT_DELETE]) 
            && $subject->getOwner()->getId() !== $user->getId()) {
            $users[] = $subject->getOwner();
        }

        $this->em->getFilters()->enable('softdeleteable');

        $users = array_filter($users, fn($u) => is_string($u) || !$u->isDeleted());

        return $users;
    }

    public function processAndNotify(User $user, UserActionEnum $action, EntityInterface $subject): void
    {
        $notifyUsers = $this->getUsersToNotify($user, $action, $subject);
        $notificationClass = Notification::getSubclassByEntity($subject);
        $notifications = $this->em->getRepository($notificationClass)->findBy([
            'action' => $action->isBaseAction() ? $action : $action->getBaseAction(),
            'subject' => $subject,
        ]);

        $persistCounter = 0;
        $allNotificationIds = [];
        $allUserIds = [];
        $roles = [];
        foreach ($notifyUsers as $user) {
            $userNotification = null;
            $isRole = is_string($user);
            $compare = null;
            if ($isRole) {
                $roles[] = $user;
                $compare = function(Notification $n) use ($user) {
                    return $n->getRole() === $user;
                };
            } else {
                $allUserIds[] = $userId = (string) $user->getId();
                $compare = function(Notification $n) use ($userId) {
                    return ((string) $n->getUser()->getId()) === $userId;
                };
            }

            foreach ($notifications as $notification) {
                if ($compare($notification)) {
                    $userNotification = $notification;
                    break;
                }
            }

            if (!$userNotification) {
                $userNotification = (new $notificationClass())
                    ->setSubject($subject)
                    ->setAction($action)
                    ->setCount(0);
                
                if ($isRole) {
                    $userNotification->setRole($user);
                } else {
                    $userNotification->setUser($user);
                }
            }
            
            $addValue = $action->isBaseAction() ? 1 : -1;
            $userNotification
                ->setCount($userNotification->getCount() + $addValue)
                ->setSeen(false);
            
            if ($action && UserSubjectAction::hasAdditionalData($action)) {
                $userNotification->setUserSubjectActions($subject->getUserSubjectActions());
            }

            if ($userNotification->getCount() !== null && $userNotification->getCount() > 0) {
                $this->em->persist($userNotification);

                $allNotificationIds[] = $userNotification->getId();
            } elseif ($userNotification->getId() ?? null) {
                $this->em->remove($userNotification);
            }
            $persistCounter++;

            if ($persistCounter % 100 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();

        $roleUserIds = [];
        if ($roles) {
            $roles = array_unique($roles);
            foreach ($roles as $role) {
                $userIds = $this->em->getRepository(User::class)->findIdsByRole($role);
                $roleUserIds = array_merge($roleUserIds, $userIds);
            }
            $roleUserIds = array_unique($allUserIds);
        }
        $allUserIds = array_merge($allUserIds, $roleUserIds);

        $this->notify($allUserIds, $allNotificationIds);
    }
}