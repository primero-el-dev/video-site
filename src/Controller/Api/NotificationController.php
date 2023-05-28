<?php

namespace App\Controller\Api;

use App\Entity\Notification\Notification;
use App\Repository\Notification\NotificationRepository;
use App\Traits\EntityManagerTrait;
use App\Traits\TranslatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notification')]
class NotificationController extends AbstractController
{
    use EntityManagerTrait;
    use TranslatorTrait;

    public function __construct(private NotificationRepository $notificationRepository) {}

    #[Route('/', name: 'api_notification_index')]
    public function index(): Response
    {
        return $this->json(
            [
                'data' => $this->notificationRepository->findForUser($this->getUser()),
            ],
            context: ['groups' => ['notification']],
        );
    }

    #[ParamConverter('notification', class: Notification::class)]
    #[Route('/{id}/see', name: 'api_notification_see', methods: 'POST')]
    public function see(Notification $notification): Response
    {
        $notification->setSeen(true);

        $this->notificationRepository->save($notification, true);

        return $this->json(
            [
                'message' => $this->translator->trans('controller.notification.see.success'),
                'data' => $notification,
            ],
            context: ['groups' => ['notification']],
        );
    }

    #[Route('/see', name: 'api_notification_see_all', methods: 'POST')]
    public function seeAll(): Response
    {
        $notifications = $this->notificationRepository->findForUser($this->getUser());

        $counter = 0;
        foreach ($notifications as $notification) {
            $notification->setSeen(true);
            $counter++;

            $this->em->persist($notification);
            if ($counter % 100 === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();

        return $this->json(['message' => $this->translator->trans('controller.notification.seeAll.success')]);
    }
}
