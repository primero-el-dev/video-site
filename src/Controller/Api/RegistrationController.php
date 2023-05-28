<?php

namespace App\Controller\Api;

use App\Entity\Token\RegistrationConfirmationToken;
use App\Entity\Token\Token;
use App\Messenger\Command\SendRegistrationConfirmationEmailCommand;
use App\Repository\Token\RegistrationConfirmationTokenRepository;
use App\Repository\UserRepository;
use App\Traits\TranslatorTrait;
use App\Util\FileHandlerInterface;
use Doctrine\DBAL\ConnectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\RegistrationType;
use App\Entity\User;
use App\Exception\ApiException;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use TranslatorTrait;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $commandBus,
        private RegistrationConfirmationTokenRepository $registrationConfirmationTokenRepository,
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private FileHandlerInterface $fileHandler,
    ) {}

    #[Route('/api/registration', name: 'api_registration', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        // dd(array_merge($request->request->all(), $request->files->all()));
        $form->submit(array_merge($request->request->all(), $request->files->all()));
        if ($form->isValid()) {
            if ($image = $form->get('image')->getData()) {
                $user->setImagePath($this->fileHandler->uploadImage($image));
            }
            if ($background = $form->get('background')->getData()) {
                $user->setBackgroundPath($this->fileHandler->uploadImage($background));
            }
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            
            $this->em->beginTransaction();
            try {
                $this->em->persist($user);

                $token = new RegistrationConfirmationToken($user);

                $this->em->persist($token);
                $this->em->flush();
                $this->em->commit();

                $this->dispatchSendRegistrationConfirmationCommand($user, $token);

                return $this->json(['message' => $this->translator->trans('controller.registration.register.success')]);
            } catch (ConnectionException $e) {
                $this->em->close();
                $this->em->rollBack();

                throw $e;
            }
        }
        foreach ($form->getErrors() as $e) {
            dd($e);
        }
        
        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    #[ParamConverter('token', class: RegistrationConfirmationToken::class, converter: 'valid_token_param_converter')]
    #[Route('/api/registration/confirm', name: 'api_registration_confirm')]
    public function confirm(RegistrationConfirmationToken $token, Request $request): JsonResponse
    {
        $user = $token->getOwner();
        $user->setVerified(true);
        
        $this->em->wrapInTransaction(function() use ($user, $token): void {
            $this->em->remove($token);
            $this->em->persist($user);
        });

        $link = $this->urlGenerator->generate('app_home', ['path' => 'login'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json(['message' => $this->translator->trans('controller.registration.confirm.success')], 200, [
            'refresh' => '5; url=' . $link,
        ]);
    }

    #[Route('/api/registration/confirm/resend', name: 'api_registration_confirm_resend', methods: ['POST'])]
    public function confirmResend(Request $request): JsonResponse
    {
        $content = $this->getJsonContentOrThrowApiException($request);
        $email = $content['email'] ?? null;
        if (!$email) {
            throw new ApiException($this->translator->trans('controller.registration.confirmResend.missingEmail'));
        }

        $user = $this->userRepository->findOneByEmail($email);
        if ($user) {
            $token = new RegistrationConfirmationToken($user);

            $this->em->persist($token);
            $this->em->flush();

            $this->dispatchSendRegistrationConfirmationCommand($user, $token);
        }

        return $this->json(['message' => $this->translator->trans('controller.registration.confirmResend.success')]);
    }

    private function dispatchSendRegistrationConfirmationCommand(User $user, Token $token): void
    {
        $this->commandBus->dispatch(new SendRegistrationConfirmationEmailCommand(
            $user, 
            $this->createConfirmationLink($token)
        ));
    }

    private function createConfirmationLink(Token $token): string
    {
        return $this->urlGenerator->generate(
            'api_registration_confirm', 
            ['token' => $token->getValue()], 
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
