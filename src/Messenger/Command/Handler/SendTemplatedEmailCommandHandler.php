<?php

namespace App\Messenger\Command\Handler;

use App\Messenger\Command\Handler\CommandHandlerInterface;
use App\Messenger\Command\SendTemplatedEmailCommand;
use App\Repository\UserRepository;
use App\Traits\ContainerTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Throwable;

class SendTemplatedEmailCommandHandler implements CommandHandlerInterface
{
    use ContainerTrait;

    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository,
        private LoggerInterface $appErrorLogger,
    ) {}

    public function __invoke(SendTemplatedEmailCommand $command): void
    {
        try {
            $users = $this->userRepository->findById($command->userIds);
            
            foreach ($users as $user) {
                $params = array_merge($command->additionalParams, ['user' => $user]);

                $email = (new TemplatedEmail())
                    ->from($_ENV['APP_MAIL'])
                    ->to($command->secondaryEmail ? $user->getEmail() : $user->getEmail())
                    ->subject($command->subject)
                    ->htmlTemplate($command->templatePath)
                    ->context($params);

                $this->mailer->send($email);
            }
        } catch (Throwable $e) {
            $this->appErrorLogger->error($e->getMessage());
        }
    }
}