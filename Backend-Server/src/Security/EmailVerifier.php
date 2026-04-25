<?php

namespace App\Security;

use App\Entity\User;
use App\Job\Message\SendEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private MessageBusInterface $messageBus;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus,
        ParameterBagInterface $parameterBag,
    ) {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
        $this->parameterBag = $parameterBag;
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, string $locale): void
    {
        $context = array();
//        $visualSettingKeyLogoImageUrl = $this->visualSettingService->getSettingByKey('LOGO');

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
        $context['locale'] = $locale;
        $context['appBaseUrl'] = $this->parameterBag->get('app_base_url');
//        $context['logoImageUrl'] = $visualSettingKeyLogoImageUrl?->getImageFileUrl();

        $this->messageBus->dispatch(new SendEmailMessage(
            $user->getEmail(),
            $this->translator->trans('confirmYourEmailAddress'),
            'registration/confirmation_email.html.twig',
            $context
        ));
    }

    /**
     * @param Request $request
     * @param User $user
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $dateTime = new \DateTime();
        $user->setVerifiedAt($dateTime);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function sendEmailResetPassword(User $user, ResetPasswordToken $resetToken, string $locale): void
    {
        $visualSettingKeyLogoImageUrl = $this->visualSettingService->getSettingByKey('LOGO');
        $context = [
            'resetToken' => $resetToken,
            'locale' => $locale,
            'appBaseUrl' => $this->parameterBag->get('app_base_url'),
            'url' => $this->parameterBag->get('website_base_url') . '/auth/reset-password?token=' . $resetToken->getToken(),
            'logoImageUrl' => $visualSettingKeyLogoImageUrl?->getImageFileUrl(),
        ];

        $this->messageBus->dispatch(new SendEmailMessage(
            $user->getEmail(),
            $this->translator->trans('resetYourPassword'),
            'reset_password/email.html.twig',
            $context
        ));
    }
}
