<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: QuoteUser::class)]
    private Collection $quoteUsers;

    #[ORM\OneToMany(mappedBy: 'updated_by', targetEntity: Product::class)]
    private Collection $modified_product;

    #[ORM\OneToMany(mappedBy: 'created_by', targetEntity: Product::class)]
    private Collection $created_product;

    #[ORM\OneToMany(mappedBy: 'created_by', targetEntity: Category::class)]
    private Collection $created_categories;

    #[ORM\OneToMany(mappedBy: 'updated_by', targetEntity: Category::class)]
    private Collection $modified_categories;

    public function __construct()
    {
        $this->quoteUsers = new ArrayCollection();
        $this->modified_product = new ArrayCollection();
        $this->created_product = new ArrayCollection();
        $this->created_categories = new ArrayCollection();
        $this->modified_categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection<int, Product>
     */
    public function getModifiedProduct(): Collection
    {
        return $this->modified_product;
    }

    public function addModifiedProduct(Product $modifiedProduct): static
    {
        if (!$this->modified_product->contains($modifiedProduct)) {
            $this->modified_product->add($modifiedProduct);
            $modifiedProduct->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeModifiedProduct(Product $modifiedProduct): static
    {
        if ($this->modified_product->removeElement($modifiedProduct)) {
            // set the owning side to null (unless already changed)
            if ($modifiedProduct->getUpdatedBy() === $this) {
                $modifiedProduct->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getCreatedProduct(): Collection
    {
        return $this->created_product;
    }

    public function addCreatedProduct(Product $createdProduct): static
    {
        if (!$this->created_product->contains($createdProduct)) {
            $this->created_product->add($createdProduct);
            $createdProduct->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedProduct(Product $createdProduct): static
    {
        if ($this->created_product->removeElement($createdProduct)) {
            // set the owning side to null (unless already changed)
            if ($createdProduct->getCreatedBy() === $this) {
                $createdProduct->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCreatedCategories(): Collection
    {
        return $this->created_categories;
    }

    public function addCreatedCategory(Category $createdCategory): static
    {
        if (!$this->created_categories->contains($createdCategory)) {
            $this->created_categories->add($createdCategory);
            $createdCategory->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedCategory(Category $createdCategory): static
    {
        if ($this->created_categories->removeElement($createdCategory)) {
            // set the owning side to null (unless already changed)
            if ($createdCategory->getCreatedBy() === $this) {
                $createdCategory->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getModifiedCategories(): Collection
    {
        return $this->modified_categories;
    }

    public function addModifiedCategory(Category $modifiedCategory): static
    {
        if (!$this->modified_categories->contains($modifiedCategory)) {
            $this->modified_categories->add($modifiedCategory);
            $modifiedCategory->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeModifiedCategory(Category $modifiedCategory): static
    {
        if ($this->modified_categories->removeElement($modifiedCategory)) {
            // set the owning side to null (unless already changed)
            if ($modifiedCategory->getUpdatedBy() === $this) {
                $modifiedCategory->setUpdatedBy(null);
            }
        }

        return $this;
    }
}
