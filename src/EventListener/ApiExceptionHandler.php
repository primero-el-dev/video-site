<?php

namespace App\EventListener;

use App\Traits\TranslatorTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Exception\ApiException;

class ApiExceptionHandler implements EventSubscriberInterface
{
    use TranslatorTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'handleException',
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getRequestUri(), '/api')) {
            return;
        }
        
        $e = $event->getThrowable();
dd($e);
        if ($e instanceof ApiException) {
            $response = new JsonResponse($e->presentData(), $e->getCode());
        } elseif ($e instanceof HttpException) {
            $message = ($_ENV['APP_ENV'] === 'dev') 
                ? $e->getMessage() 
                : $this->translator->trans('common.http.error_' . $e->getStatusCode());
            $response = new JsonResponse(['error' => $message], $e->getStatusCode(), $e->getHeaders());
        } else {
            $response = new JsonResponse([
                'error' => ($_ENV['APP_ENV'] === 'dev') ? $e->getMessage() : 'Internal server error.'
            ], 500);
        }
        
        $event->setResponse($response);
    }
}