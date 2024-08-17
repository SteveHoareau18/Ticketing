<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: Ticket::class)]
    private Collection $tickets;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: User::class)]
    private Collection $Users;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->Users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setService($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getService() === $this) {
                $ticket->setService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->Users;
    }

    public function addUser(User $user): static
    {
        if (!$this->Users->contains($user)) {
            $this->Users->add($user);
            $user->setService($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->Users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getService() === $this) {
                $user->setService(null);
            }
        }

        return $this;
    }
}
