<?php

namespace App\Service;

use App\Constant\RolesGroupsIdentifiers;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository               $userRepository,
        private readonly UserPasswordHasherInterface  $passwordHasher,
        private readonly EmailVerifier                $emailVerifier,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly TranslatorInterface          $translator,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly ParameterBagInterface $parameterBag,

    ) {}

    public function isAdmin(User $user): bool
    {
        return $this->getUserType($user) == "SUPER_ADMIN";
    }

    public function getUserType(User $user): string
    {
        $rolesGroups = $user->getRolesGroups();

        $rolesGroup = $rolesGroups->findFirst(function ($key, $rolesGroup) use ($user) {
            return $rolesGroup->getIdentifier() == RolesGroupsIdentifiers::User->value
                || $rolesGroup->getIdentifier() == RolesGroupsIdentifiers::SuperAdmin->value;
        });

        if (!$rolesGroup) {
            return "SUPER_ADMIN";
        }

        $userIdentifier = RolesGroupsIdentifiers::User->value;

        if ($rolesGroup->getIdentifier() == $userIdentifier) {
            return "USER";
        }

        return "SUPER_ADMIN";
    }

    public function canAccessToDashboard(User $user): bool
    {
        $roles = $user->getRoles();
        if (in_array('ACCESS_TO_DASHBOARD', $roles, true)) {
            return true;
        }

        // Super Admin from migration has ROLE_* but not ACCESS_TO_DASHBOARD; they must still sign in.
        if ($this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function generateResetToken(string $email, string $locale): ?ResetPasswordToken
    {
        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);

        if ($user) {
            try {
                $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            } catch (ResetPasswordExceptionInterface $e) {
                return null;
            }

            $this->emailVerifier->sendEmailResetPassword($user, $resetToken, $locale);
            return $resetToken;
        }
        return null;
    }

    public function validateTokenAndFetchUser(string $token): ?object
    {
        try {
            return $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {

            return null;
        }
    }

    public function resetPassword(User $user, FormInterface $form, $token): void
    {
        $this->resetPasswordHelper->removeResetRequest($token);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());

        $user->setPassword($hashedPassword);
        $this->userRepository->update($user);
    }

    public function getById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }
}
