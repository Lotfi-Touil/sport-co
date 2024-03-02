<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Company $company = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: QuoteUser::class)]
    private Collection $quoteUsers;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: InvoiceUser::class)]
    private Collection $invoiceUsers;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'employe', targetEntity: PageAccess::class)]
    private Collection $pageAccesses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    public function __construct()
    {
        $this->quoteUsers = new ArrayCollection();
        $this->invoiceUsers = new ArrayCollection();
        $this->pageAccesses = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, QuoteUser>
     */
    public function getQuoteUsers(): Collection
    {
        return $this->quoteUsers;
    }

    public function addQuoteUser(QuoteUser $quoteUser): static
    {
        if (!$this->quoteUsers->contains($quoteUser)) {
            $this->quoteUsers->add($quoteUser);
            $quoteUser->setCreator($this);
        }

        return $this;
    }

    public function removeQuoteUser(QuoteUser $quoteUser): static
    {
        if ($this->quoteUsers->removeElement($quoteUser)) {
            // set the owning side to null (unless already changed)
            if ($quoteUser->getCreator() === $this) {
                $quoteUser->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceUser>
     */
    public function getInvoiceUsers(): Collection
    {
        return $this->invoiceUsers;
    }

    public function addInvoiceUser(InvoiceUser $invoiceUser): static
    {
        if (!$this->invoiceUsers->contains($invoiceUser)) {
            $this->invoiceUsers->add($invoiceUser);
            $invoiceUser->setCreator($this);
        }

        return $this;
    }

    public function removeInvoiceUser(InvoiceUser $invoiceUser): static
    {
        if ($this->invoiceUsers->removeElement($invoiceUser)) {
            // set the owning side to null (unless already changed)
            if ($invoiceUser->getCreator() === $this) {
                $invoiceUser->setCreator(null);
            }
        }

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

    /**
     * @return Collection<int, PageAccess>
     */
    public function getPageAccesses(): Collection
    {
        return $this->pageAccesses;
    }

    public function addPageAccess(PageAccess $pageAccess): static
    {
        if (!$this->pageAccesses->contains($pageAccess)) {
            $this->pageAccesses->add($pageAccess);
            $pageAccess->setEmploye($this);
        }

        return $this;
    }

    public function removePageAccess(PageAccess $pageAccess): static
    {
        if ($this->pageAccesses->removeElement($pageAccess)) {
            // set the owning side to null (unless already changed)
            if ($pageAccess->getEmploye() === $this) {
                $pageAccess->setEmploye(null);
            }
        }

        return $this;
    }

    // Getter
    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    // Setter
    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

}
