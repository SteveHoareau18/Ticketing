<?php

namespace App\Service;

use App\Entity\MailConfiguration;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

class MailService
{
    private Mailer $mailer;

    public function __construct(MailConfiguration $configuration)
    {
        $this->mailer = new Mailer(Transport::fromDsn("smtp://" . urlencode($configuration->getLogin()) . ":" . urlencode($configuration->getPassword()) . "@" . urlencode($configuration->getSmtpAddress()) . ":" . $configuration->getSmtpPort()));
    }

    /**
     * @return Mailer
     */
    public function getMailer(): Mailer
    {
        return $this->mailer;
    }

}