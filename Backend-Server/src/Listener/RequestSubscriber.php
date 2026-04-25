<?php

namespace App\Listener;

use App\Service\SystemLanguageService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Translation\LocaleSwitcher;

class RequestSubscriber implements EventSubscriberInterface
{
    private SystemLanguageService $systemLanguageService;
    private LocaleSwitcher $localeSwitcher;

    public function __construct(SystemLanguageService $systemLanguageService, LocaleSwitcher $localeSwitcher)
    {
        $this->systemLanguageService = $systemLanguageService;
        $this->localeSwitcher = $localeSwitcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = $request->headers->get('x-locale');
        if ($locale && in_array($locale, $this->systemLanguageService->getLocales())) {
            $this->localeSwitcher->setLocale($locale);
            $event->getRequest()->setLocale($locale);
        }
    }
}
