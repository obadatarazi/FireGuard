<?php

namespace App\Security;

use App\Entity\User;
use App\Service\AuthService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminChecker implements UserCheckerInterface
{

    private TranslatorInterface $translator;
    private AuthService $authService;

    public function __construct(TranslatorInterface $translator, AuthService $authService, )
    {
        $this->translator = $translator;
        $this->authService = $authService;
    }

    public function checkPreAuth(UserInterface $user)
    {
        // TODO: Implement checkPreAuth() method.
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user && $this->authService->canAccessToDashboard($user)) {
            return $user;
        }

        throw new AccessDeniedHttpException('access_denied');
    }
}
