<?php

namespace App\Entity;

use App\Repository\MailConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailConfigurationRepository::class)]
class MailConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $login = null;

    #[ORM\Column(length: 150)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $smtpAddress = null;

    #[ORM\Column(length: 10)]
    private ?string $smtpPort = null;

    #[ORM\Column]
    private ?bool $smtpTls = null;

    #[ORM\Column(length: 255)]
    private ?string $ccAddress = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSmtpAddress(): ?string
    {
        return $this->smtpAddress;
    }

    public function setSmtpAddress(string $smtpAddress): self
    {
        $this->smtpAddress = $smtpAddress;

        return $this;
    }

    public function getSmtpPort(): ?string
    {
        return $this->smtpPort;
    }

    public function setSmtpPort(string $smtpPort): self
    {
        $this->smtpPort = $smtpPort;

        return $this;
    }

    public function isSmtpTls(): ?bool
    {
        return $this->smtpTls;
    }

    public function setSmtpTls(bool $smtpTls): self
    {
        $this->smtpTls = $smtpTls;

        return $this;
    }


    public function getCcAddress(): ?string
    {
        return $this->ccAddress;
    }

    public function setCcAddress(string $ccAddress): self
    {
        $this->ccAddress = $ccAddress;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}