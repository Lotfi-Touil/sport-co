<?php

namespace App\Entity;

use App\Repository\QuoteUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteUserRepository::class)]
class QuoteUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quoteUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quote $quote = null;

    #[ORM\ManyToOne(inversedBy: 'quoteUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creatorUser = null;

    #[ORM\ManyToOne(inversedBy: 'quoteUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customerUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getCreatorUser(): ?User
    {
        return $this->creatorUser;
    }

    public function setCreatorUser(?User $creatorUser): static
    {
        $this->creatorUser = $creatorUser;

        return $this;
    }

    public function getCustomerUser(): ?User
    {
        return $this->customerUser;
    }

    public function setCustomerUser(?User $customerUser): static
    {
        $this->customerUser = $customerUser;

        return $this;
    }
}
