<?php

namespace App\Service;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailService
{
    private MailerInterface $mailer;
    private ParameterBagInterface $parameterBag;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    public function sendEmail(string $email, string $subject, $htmlTemplate, $context)
    {   
        $email = (new TemplatedEmail())
            ->from(new Address($this->parameterBag->get('sender_email'), $this->parameterBag->get('sender_name')))
            ->to($email)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate);

        $emailContext = $email->getContext();
      
        foreach ($context as $key => $value) {
            $emailContext[$key] = $value;
        }
       
        $email->context($emailContext);
      
        $this->mailer->send($email);
     
    }
}