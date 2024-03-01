<?php

namespace App\Entity;

use App\Repository\BasicEmailTemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasicEmailTemplateRepository::class)]
class BasicEmailTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $subjet = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\OneToOne()]
    #[ORM\JoinColumn(nullable: false)]
    private ?EmailType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubjet(): ?string
    {
        return $this->subjet;
    }

    public function setSubjet(string $subjet): static
    {
        $this->subjet = $subjet;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getType(): ?EmailType
    {
        return $this->type;
    }

    public function setType(EmailType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
