<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'quotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?QuoteStatus $quoteStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 4)]
    private ?string $totalAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 4)]
    private ?string $subtotal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $SubmittedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: QuoteProduct::class, cascade: ['persist'])]
    private Collection $quoteProducts;

    public function __construct()
    {
        $this->quoteProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuoteStatus(): ?QuoteStatus
    {
        return $this->quoteStatus;
    }

    public function setQuoteStatus(?QuoteStatus $quoteStatus): static
    {
        $this->quoteStatus = $quoteStatus;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->SubmittedAt;
    }

    public function setSubmittedAt(?\DateTimeInterface $SubmittedAt): static
    {
        $this->SubmittedAt = $SubmittedAt;

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?\DateTimeInterface $expiryDate): static
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function incrementSubtotal(float $value): static
    {
        $this->setSubtotal($this->getSubtotal() + $value);

        return $this;
    }

    public function incrementTotalAmount(float $value): static
    {
        $this->setTotalAmount($this->getTotalAmount() + $value);

        return $this;
    }

    /**
     * @return Collection<int, QuoteProduct>
     */
    public function getQuoteProducts(): Collection
    {
        return $this->quoteProducts;
    }

    public function addQuoteProduct(QuoteProduct $quoteProduct): static
    {
        if (!$this->quoteProducts->contains($quoteProduct)) {
            $this->quoteProducts->add($quoteProduct);
            $quoteProduct->setQuote($this);
        }

        return $this;
    }

    public function removeQuoteProduct(QuoteProduct $quoteProduct): static
    {
        if ($this->quoteProducts->removeElement($quoteProduct)) {
            // set the owning side to null (unless already changed)
            if ($quoteProduct->getQuote() === $this) {
                $quoteProduct->setQuote(null);
            }
        }

        return $this;
    }
}
