<?php

namespace App\Job\MessageHandler;

use App\Job\Message\SendEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Service\MailService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Translation\LocaleSwitcher;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    private MailService $mailService;
    private LocaleSwitcher $localeSwitcher;

    public function __construct(MailService $mailService, LocaleSwitcher $localeSwitcher)
    {
        $this->mailService = $mailService;
        $this->localeSwitcher = $localeSwitcher;
    }

    public function __invoke(SendEmailMessage $message)
    {
        if (isset($message->getBody()['locale']))
            $this->localeSwitcher->setLocale($message->getBody()['locale']);
        
        $this->mailService->sendEmail(
            $message->getTo(),
            $message->getSubject(),
            $message->getHtmlTemplate(),
            $message->getBody(),
        );

    }
}
