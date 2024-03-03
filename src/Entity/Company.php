<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Customer::class)]
    private Collection $customers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: User::class)]
    private Collection $users;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true , options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true )]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: EmailTemplate::class, orphanRemoval: true)]
    private Collection $emailTemplates;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: InvoiceStatus::class)]
    private Collection $invoiceStatuses;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: QuoteStatus::class)]
    private Collection $quoteStatuses;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: ProductCategory::class)]
    private Collection $productCategories;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Product::class)]
    private Collection $products;

    public function __construct()
    {
        $this->customers = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->emailTemplates = new ArrayCollection();
        $this->invoiceStatuses = new ArrayCollection();
        $this->quoteStatuses = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomers(): Collection
    {
        return $this->customers;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customers->contains($customer)) {
            $this->customers->add($customer);
            $customer->setCompany($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customers->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getCompany() === $this) {
                $customer->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

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

    /**
     * @return Collection<int, EmailTemplate>
     */
    public function getEmailTemplates(): Collection
    {
        return $this->emailTemplates;
    }

    public function addEmailTemplate(EmailTemplate $emailTemplate): static
    {
        if (!$this->emailTemplates->contains($emailTemplate)) {
            $this->emailTemplates->add($emailTemplate);
            $emailTemplate->setCompany($this);
        }

        return $this;
    }

    public function removeEmailTemplate(EmailTemplate $emailTemplate): static
    {
        if ($this->emailTemplates->removeElement($emailTemplate)) {
            // set the owning side to null (unless already changed)
            if ($emailTemplate->getCompany() === $this) {
                $emailTemplate->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceStatus>
     */
    public function getInvoiceStatuses(): Collection
    {
        return $this->invoiceStatuses;
    }

    public function addInvoiceStatus(InvoiceStatus $invoiceStatus): static
    {
        if (!$this->invoiceStatuses->contains($invoiceStatus)) {
            $this->invoiceStatuses->add($invoiceStatus);
            $invoiceStatus->setCompany($this);
        }

        return $this;
    }

    public function removeInvoiceStatus(InvoiceStatus $invoiceStatus): static
    {
        if ($this->invoiceStatuses->removeElement($invoiceStatus)) {
            // set the owning side to null (unless already changed)
            if ($invoiceStatus->getCompany() === $this) {
                $invoiceStatus->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuoteStatus>
     */
    public function getQuoteStatuses(): Collection
    {
        return $this->quoteStatuses;
    }

    public function addQuoteStatus(QuoteStatus $quoteStatus): static
    {
        if (!$this->quoteStatuses->contains($quoteStatus)) {
            $this->quoteStatuses->add($quoteStatus);
            $quoteStatus->setCompany($this);
        }

        return $this;
    }

    public function removeQuoteStatus(QuoteStatus $quoteStatus): static
    {
        if ($this->quoteStatuses->removeElement($quoteStatus)) {
            // set the owning side to null (unless already changed)
            if ($quoteStatus->getCompany() === $this) {
                $quoteStatus->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): static
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories->add($productCategory);
            $productCategory->setCompany($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): static
    {
        if ($this->productCategories->removeElement($productCategory)) {
            // set the owning side to null (unless already changed)
            if ($productCategory->getCompany() === $this) {
                $productCategory->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCompany($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCompany() === $this) {
                $product->setCompany(null);
            }
        }

        return $this;
    }
}
