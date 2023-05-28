<?php

namespace App\Notification;

use App\Entity\Enum\UserActionEnum;
use App\Entity\Interfaces\EntityInterface;
use App\Entity\Notification\Notification;
use App\Entity\User;

interface NotificationManagerInterface
{
    public const VIDEO_CREATE_ACTION = 'video_create';
    public const COMMENT_CREATE_ACTION = 'comment_create';
    public const VIDEO_DELETE_ACTION = 'video_delete';
    public const COMMENT_DELETE_ACTION = 'comment_delete';

    /**
     * @param User[]|string[] $users - users or user ids
     * @param Notification[] $notifications
     */
    public function notify(array $users, array $notifications): void;

    public function processAndNotify(User $user, UserActionEnum $action, EntityInterface $subject): void;
}