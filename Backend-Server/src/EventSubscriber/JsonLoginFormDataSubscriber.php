<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * json_login only accepts application/json. Swagger UI often sends multipart/form-data or
 * x-www-form-urlencoded, which yields "Invalid JSON." This subscriber rewrites those requests
 * to a JSON body before the security authenticator runs.
 */
final class JsonLoginFormDataSubscriber implements EventSubscriberInterface
{
    private const LOGIN_PATHS = [
        '/api/admin/login',
        '/api/mobile/login',
    ];

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 100]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            return;
        }

        if (!\in_array($request->getPathInfo(), self::LOGIN_PATHS, true)) {
            return;
        }

        $content = $request->getContent();
        $decoded = null;
        if ($content !== '') {
            try {
                $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $decoded = null;
            }
        }

        $hasJsonCreds = \is_array($decoded)
            && isset($decoded['username'], $decoded['password'])
            && \is_string($decoded['username'])
            && \is_string($decoded['password']);

        if ($hasJsonCreds) {
            return;
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        if (!\is_string($username) || !\is_string($password) || $username === '' || $password === '') {
            return;
        }

        $payload = json_encode(
            ['username' => $username, 'password' => $password],
            JSON_THROW_ON_ERROR
        );

        $server = $request->server->all();
        $server['CONTENT_TYPE'] = 'application/json';
        $server['HTTP_CONTENT_TYPE'] = 'application/json';

        $request->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $server,
            $payload
        );
    }
}
