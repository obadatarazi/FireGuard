<?php

namespace App\Job\Message;


class SendEmailMessage
{
    private $to;
    private $subject;
    private $body;
    private string $htmlTemplate;

    public function __construct(string $to, string $subject, string $htmlTemplate, array $body)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->htmlTemplate = $htmlTemplate;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }

    public function getBody(): array
    {
        return $this->body;
    }
}
