<?php

namespace App\Websocket;
 
use App\Entity\Notification\Notification;
use App\Interfaces\HasWsAuthTokenInterface;
use App\Repository\Notification\NotificationRepository;
use App\Repository\UserRepository;
use App\Traits\EntityManagerTrait;
use App\Traits\WsAuthTokenTrait;
use App\Websocket\NotificationHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
 
class NotificationHandler implements NotificationHandlerInterface, HttpServerInterface, HasWsAuthTokenInterface
{
    use EntityManagerTrait;
    use WsAuthTokenTrait;

    /**
     * @param array<string, ConnectionInterface> $connections - [userId => $conn]
     */
    protected array $connections = [];
 
    public function __construct(
        private SerializerInterface $serializer,
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
    ) {
        $this->connections = [];
    }

    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null): void
    {
        if ($userId = $conn->Session->get('userId')) {
            $user = $this->userRepository->find($userId);
            if (!$user) {
                return;
            }
            
            $this->connections[$userId] = $conn;

            $this->disableSoftDelete();
            $conn->send($this->serializeNotifications($this->notificationRepository->findForUser($user, false)));
            $this->enableSoftDelete();
        }
    }
 
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $headers = $from->httpRequest->getHeaders();
        [$token] = sscanf($headers['Authorization'][0], 'Bearer %s');
        if ($token !== $this->getWsAuthToken()) {
            return;
        }

        $data = json_decode($msg, true);
        if (!$data) {
            return;
        }

        $this->disableSoftDelete();
        $this->em->clear();
        $notification = $this->notificationRepository->find($data['notification_id']);
        if (!$notification) {
            $this->enableSoftDelete();
            return;
        }
        $this->enableSoftDelete();
        
        $notifications = $this->serializeNotifications([$notification]);
        
        foreach ($data['user_ids'] as $userId) {
            $conn = $this->connections[$userId] ?? null;
            if ($conn) {
                $conn->send($notifications);
            }
        }
    }
 
    public function onClose(ConnectionInterface $conn): void
    {
        if ($userId = $conn->Session->get('userId')) {
            unset($this->connections[$userId]);
        }
    }
 
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        dd($e->getTrace());
        dd($e->getMessage());
        $conn->send($e->getMessage());
    }

    /**
     * @param Notification[] $notifications
     */
    private function serializeNotifications(array $notifications): string
    {
        return $this->serializer->serialize($notifications, 'json', ['groups' => [
            'notification', 
            'video', 
            'comment', 
            'comment_extended', 
            'user', 
            'timestamp', 
            'notification_actions', 
            'user_action',
            'additional_data',
        ]]);
    }

    private function disableSoftDelete(): void
    {
        if ($this->em->getFilters()->isEnabled('softdeleteable')) {
            $this->em->getFilters()->disable('softdeleteable');
        }
    }

    private function enableSoftDelete(): void
    {
        if (!$this->em->getFilters()->isEnabled('softdeleteable')) {
            $this->em->getFilters()->enable('softdeleteable');
        }
    }
}