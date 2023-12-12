<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 4)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $tax_rate = null;
    
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $stripeProductId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $stripePriceId = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: QuoteProduct::class)]
    private Collection $quoteProducts;

    public function __construct()
    {
        $this->quoteProducts = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTaxRate(): ?string
    {
        return $this->tax_rate;
    }

    public function setTaxRate(string $tax_rate): static
    {
        $this->tax_rate = $tax_rate;

        return $this;
    }

    public function getPriceHT()
    {
        return $this->getPrice() * (1 - $this->getTaxRate() / 100);
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
            $quoteProduct->setProduct($this);
        }

        return $this;
    }

    public function removeQuoteProduct(QuoteProduct $quoteProduct): static
    {
        if ($this->quoteProducts->removeElement($quoteProduct)) {
            // set the owning side to null (unless already changed)
            if ($quoteProduct->getProduct() === $this) {
                $quoteProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(?string $stripeProductId): self
    {
        $this->stripeProductId = $stripeProductId;
        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(?string $stripePriceId): self
    {
        $this->stripePriceId = $stripePriceId;
        return $this;
    }
}
