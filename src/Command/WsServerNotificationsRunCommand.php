<?php

namespace App\Command;

use App\Interfaces\HasWsAuthTokenInterface;
use App\Traits\EntityManagerTrait;
use App\Traits\WsAuthTokenTrait;
use App\Websocket\NotificationHandler;
use App\Websocket\NotificationHandlerInterface;
use Ratchet\App;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Session\SessionProvider;
use Ratchet\WebSocket\WsServer;
use SessionHandlerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\Security\Core\Security;

#[AsCommand(
    name: WsServerNotificationsRunCommand::NAME,
    description: 'Run websocket server for sending notifications to users.',
)]
class WsServerNotificationsRunCommand extends Command implements HasWsAuthTokenInterface
{
    use EntityManagerTrait;
    use WsAuthTokenTrait;

    public const NAME = 'wsserver:notifications:run';

    public function __construct(
        private \SessionHandlerInterface $sessionHandler,
        private NotificationHandlerInterface $notificationHandler,
    ) {
        parent::__construct(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // phpinfo();
        // die;

        // $session = new SessionProvider(
        //     new NotificationHandler($this->security),
        //     $this->sessionHandler
        // );

        // $echoServer = new class extends \Ratchet\Server\EchoServer implements \Ratchet\Http\HttpServerInterface {
        //     public function onOpen(\Ratchet\ConnectionInterface $conn, \Psr\Http\Message\RequestInterface $request = null) {
        //         parent::onOpen($conn);
        //     }
        // };

        $port = $_ENV['WS_NOTIFICATION_PORT'];
        $output->writeln('Starting server on port ' . $port);
        
        $session = new SessionProvider(
            new WsServer($this->notificationHandler),
            $this->sessionHandler
        );

        $server = IoServer::factory(
            new HttpServer($session),
            $port
        );
        $server->run();
        
        return 0;
    }
}
