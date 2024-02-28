<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "user_detail",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      attributes = {"verb"="GET"},
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 * @Hateoas\Relation(
 *      "list",
 *      href = @Hateoas\Route(
 *          "user_list",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {"verb"="GET"},
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 *
 * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "user_create"
 *      ),
 *      attributes = {"verb"="POST"},
 *      exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 *@Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "user_update",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {"verb"="PUT"},
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "user_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      attributes = {"verb"="DELETE"},
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 * )
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[Groups(['getClients', 'getUsers'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['getClients', 'getUsers'])]
    #[Assert\NotBlank(message: "you should add user first_name")]
    #[Assert\Length(min: 1, max: 30, maxMessage: "first_name must be less than {{ limit }} caracters")]
    #[ORM\Column(length: 30)]
    private ?string $first_name = null;

    #[Groups(['getClients', 'getUsers'])]
    #[Assert\NotBlank(message: "you should add user last_name")]
    #[Assert\Length(min: 1, max: 30, maxMessage: "last_name must be less than {{ limit }} caracters")]
    #[ORM\Column(length: 30)]
    private ?string $last_name = null;

    #[Groups(['getClients', 'getUsers'])]
    #[Assert\NotBlank(message: "you should add user email")]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.',)]
    #[ORM\Column(length: 255)]
    private ?string $email = null;


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
