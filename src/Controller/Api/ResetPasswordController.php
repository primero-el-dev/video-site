<?php

namespace App\Controller\Api;

use App\Entity\Token\ResetPasswordToken;
use App\Entity\Token\Token;
use App\Exception\ApiException;
use App\Form\ResetPasswordType;
use App\Messenger\Command\SendResetPasswordEmailCommand;
use App\Repository\Token\ResetPasswordTokenRepository;
use App\Repository\UserRepository;
use App\Traits\EntityManagerTrait;
use App\Traits\GetFieldFirstFormErrorMapTrait;
use App\Traits\GetJsonContentOrThrowApiException;
use App\Traits\TranslatorTrait;
use Doctrine\DBAL\ConnectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    use EntityManagerTrait;
    use GetFieldFirstFormErrorMapTrait;
    use GetJsonContentOrThrowApiException;
    use TranslatorTrait;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private MessageBusInterface $commandBus,
        private UrlGeneratorInterface $urlGenerator,
        private ResetPasswordTokenRepository $resetPasswordTokenRepository,
    ) {}
    
    #[Route('/api/reset-password/request', name: 'api_reset_password_request')]
    public function request(Request $request): JsonResponse
    {
        $content = $this->getJsonContentOrThrowApiException($request);
        $email = $content['email'] ?? null;
        if (!$email) {
            throw new ApiException($this->translator->trans('controller.resetPassword.request.missingEmail'));
        }

        if ($user = $this->userRepository->findOneByEmail($email)) {
            $token = new ResetPasswordToken($user);

            $this->em->persist($token);
            $this->em->flush();

            $this->commandBus->dispatch(new SendResetPasswordEmailCommand(
                $user, 
                $this->createResetPasswordLink($token)
            ));
        }

        return $this->json(['message' => $this->translator->trans('controller.resetPassword.request.success')]);
    }
    
    #[ParamConverter('token', class: ResetPasswordToken::class, converter: 'valid_token_param_converter')]
    #[Route('/api/reset-password/reset', name: 'api_reset_password_reset')]
    public function reset(?ResetPasswordToken $token, Request $request): JsonResponse
    {
        // Endpoint for logged user providing old password or non-logged with valid token
        if ($this->getUser()) {
            $form = $this->createForm(ResetPasswordType::class, [], ['old_password' => true]);
            $user = $this->getUser();
        } elseif (!$token) {
            throw new ApiException($this->translator->trans('common.unauthorized'), 401);
        } else {
            $form = $this->createForm(ResetPasswordType::class, [], ['old_password' => false]);
            $user = $token->getOwner();
        }

        $content = $this->getJsonContentOrThrowApiException($request);

        $form->submit($content);
        if ($form->isValid()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);

            $this->em->wrapInTransaction(function() use ($user, $token): void {
                if ($token) {
                    $this->em->remove($token);
                }
                $this->em->persist($user);
            });

            return $this->json(['message' => $this->translator->trans('controller.resetPassword.reset.success')]);
        }

        throw new ApiException(code: 400, data: ['errors' => $this->getFieldFirstFormErrorMap($form)]);
    }

    private function createResetPasswordLink(Token $token): string
    {
        return $this->urlGenerator->generate(
            'app_home', 
            ['path' => 'reset-password/reset', 'token' => $token->getValue()], 
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}