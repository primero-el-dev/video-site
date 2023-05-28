<?php

namespace App\Notification;

use App\Entity\Notification\Notification;
use App\Entity\User;
use App\Interfaces\HasWsAuthTokenInterface;
use App\Notification\NotificationManager;
use App\Notification\NotificationManagerInterface;
use App\Traits\WsAuthTokenTrait;
use Ratchet\Client\connect;

class BrowserNotificationManager extends NotificationManager implements HasWsAuthTokenInterface
{
    use WsAuthTokenTrait;

    /**
     * {@inheritDoc}
     */
    public function notify(array $users, array $notifications): void
    {
        $userIds = ($users && $users[0] instanceof User) 
            ? array_map(fn($u) => (string) $u->getId(), $users) 
            : $users;
        $notificationIds = ($notifications && $notifications[0] instanceof Notification) 
            ? array_map(fn($u) => $u->getId(), $notifications) 
            : $notifications;
        
        $wsToken = $this->getWsAuthToken();
        $headers = ['Authorization' => "Bearer $wsToken"];
        \Ratchet\Client\connect($_ENV['WS_NOTIFICATION_URI'], [], $headers)->then(
            function($conn) use ($userIds, $notificationIds) {
                // $conn->on('message', function($msg) use ($conn) {
                //     if ($msg === 'success') {
                //         $conn->close();
                //     }
                // });

                foreach ($notificationIds as $notificationId) {
                    $conn->send(json_encode([
                        'user_ids' => $userIds, 
                        'notification_id' => $notificationId,
                    ]));
                }
                $conn->close();
            }, 
            function ($e) {
                echo "Could not connect: {$e->getMessage()}\n";
            }, 
            function () {
                echo "Connecting...\n";
            }
        );
    }
}