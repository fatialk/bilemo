<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[Groups(['getClients', 'getUsers'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['getClients', 'getUsers'])]
    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[Groups(['getClients', 'getUsers'])]
    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[Groups(['getClients', 'getUsers'])]
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Groups(['getUsers'])]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName: "id", nullable: false)]
    private ?client $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }



    /**
     * Get the value of client
     */
    public function getClient(): ?client
    {
        return $this->client;
    }

    /**
     * Set the value of client
     */
    public function setClient(?client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
