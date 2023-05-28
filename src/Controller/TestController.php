<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Enum\UserActionEnum;
use App\Entity\Tag;
use App\Entity\UserSubjectAction\UserVideoAction;
use App\Entity\Video;
use App\Form\VideoType;
use App\Interfaces\HasWsAuthTokenInterface;
use App\Messenger\Command\SendAppNotificationCommand;
use App\Messenger\Command\SendAppNotificationOnUserSubjectActionCommand;
use App\Security\PermissionManagerInterface;
use App\Security\Voter\VideoVoter;
use App\Traits\EntityManagerTrait;
use App\Traits\WsAuthTokenTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController implements HasWsAuthTokenInterface
{
    use EntityManagerTrait;
    use WsAuthTokenTrait;

    public function __construct(
        private PermissionManagerInterface $permissionManager,
        private MessageBusInterface $commandBus,
    ) {}

    #[Route('/api-test', name: 'app_test')]
    public function test(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            dd($request->files->get('snapshot'));
        }

        $video = new Video();
        $video->addTag((new Tag())->setName('fourth'));
        $form = $this->createForm(VideoType::class, $video);

        return $this->render('test.html.twig', [
            'tags' => $this->em->getRepository(Tag::class)->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/apitest2', name: 'app_test2')]
    public function test2(Request $request): Response
    {
        return $this->render('test2.html.twig', [
            'videos' => $this->em->getRepository(Video::class)->findAll(),
        ]);
    }

    #[Route('/apitest3', name: 'app_test3')]
    public function test3(Request $request): Response
    {
        $video = $this->em->getRepository(Video::class)->findAll()[0];
        $granted = $this->permissionManager->filterGranted($video, VideoVoter::PERMISSIONS);

        dd($granted);
        return $this->render('test3.html.twig', [

        ]);
    }

    #[Route('/apitest4', name: 'app_test4')]
    public function test4(Request $request): Response
    {
        // $video = $this->em->getRepository(Video::class)->findAll()[0];
        // $granted = $this->permissionManager->filterGranted($video, VideoVoter::PERMISSIONS);

        // dd($granted);
        return $this->render('test4.html.twig', [

        ]);
    }

    #[Route('/apitest5', name: 'app_test5')]
    public function test5(Request $request): Response
    {
        $request->getsession()->set('userId', (string) $this->getUser()->getId());
        // dd();
        return $this->render('test5.html.twig', [
            
        ]);
    }

    #[Route('/apitest6', name: 'app_test6')]
    public function test6(Request $request): Response
    {
        // $video = $this->em->getRepository(Video::class)->findAll()[0];
        // $user = $this->em->getRepository(User::class)->findAll()[0];
        // $entity = (new UserVideoAction())
        //     ->setUser($user)
        //     ->setSubject($video)
        //     ->setAction(UserActionEnum::REPORT);

        // $this->em->persist($entity);
        // $this->em->flush();
        
        dd($this->em->getRepository(UserVideoAction::class)->findAll());

        // $host = '127.0.0.1';  //where is the websocket server
        // $port = 3001;
        // $local = "http://localhost:8000/";  //url where this script run
        // $data = 'hello world!';  //data to be send

        // $head = "GET / HTTP/1.1"."\r\n".
        //     "Upgrade: WebSocket"."\r\n".
        //     "Connection: Upgrade"."\r\n".
        //     "Origin: $local"."\r\n".
        //     "Host: $host"."\r\n".
        //     "Sec-WebSocket-Key: asdasdaas76da7sd6asd6as7d"."\r\n".
        //     "Content-Length: ".strlen($data)."\r\n"."\r\n";
        // //WebSocket handshake
        // $sock = fsockopen($host, $port, $errno, $errstr, 2);
        // fwrite($sock, $head ) or die('error:'.$errno.':'.$errstr);
        // $headers = fread($sock, 2000);
        // echo $headers;
        // fwrite($sock, hybi10Encode($data)) or die('error:'.$errno.':'.$errstr);
        // $wsdata = fread($sock, 2000);
        // var_dump(hybi10Decode($wsdata));
        // fclose($sock);
        
        // dd($wsdata);

        // textalk/websocket
        // $client = new \WebSocket\Client("ws://localhost:3001");
        // $client->text("Hello WebSocket.org!");
        // echo $client->receive();
        // $client->close();
        // dd($request->getSession());
        // phpinfo();die;

        $userId = (string) $this->getUser()?->getId();

        $wsToken = $this->getWsAuthToken();
        $headers = ['Authorization' => "Bearer $wsToken"];
        \Ratchet\Client\connect($_ENV['WS_NOTIFICATION_URI'], [], $headers)->then(
            function($conn) use ($userId) {
                // $conn->on('message', function($msg) use ($conn) {
                //     if ($msg === 'success') {
                //         $conn->close();
                //     }
                // });

                $conn->send(json_encode([
                    'notify_users' => [$userId], 
                    'subject_id' => $userId,
                    'subject_class' => 'Comment',
                    'action' => 'report', 
                    'mode' => 'count',
                ]));
                $conn->close();
            }, 
            function ($e) {
                echo "Could not connect: {$e->getMessage()}\n";
            }, 
            function () {
                echo "Connecting...\n";
            }
        );
        // echo '123<br>';
        return $this->json([]);
    }

    #[Route('/apitest7', name: 'app_test7')]
    public function test7(Request $request): Response
    {
        // $request->getSession()->set('userId', (string) $this->getUser()->getId());

        // $this->em->getFilters()->disable('softdeleteable');
        // // $user = $this->em->getRepository(User::class)->find($request->getSession()->get('userId'));
        $subject = $this->em->getRepository(Comment::class)->find('1edf6408-f186-646e-b39d-5dce6c8feb7b');
        // $this->em->getFilters()->enable('softdeleteable');

        // $this->commandBus->dispatch(new SendAppNotificationCommand(
        //     $this->getUser(),
        //     UserActionEnum::COMMENT_DELETE, 
        //     $subject
        // ));

        $this->commandBus->dispatch(new SendAppNotificationCommand(
            $this->getUser(),
            UserActionEnum::COMMENT_CREATE,
            $subject
        ));

        return $this->json([]);
    }

    #[Route('/apitest8', name: 'app_test8')]
    public function test8(Request $request): Response
    {
        if ($request->getMethod() === 'POST')
            dd(array_merge($request->request->all(), $request->files->all()));

        $response = new Response();
        $response->setContent(<<<END
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="input" value="value">
        <input type="file" name="file" value="value">
        <button type="submit">Send</button>
    </form>
END);

        return $response;

        // return $this->render()
    }
}
