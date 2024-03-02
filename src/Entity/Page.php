<?php

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\OneToMany(fetch: 'EAGER', mappedBy: 'page', targetEntity: PageAccess::class)]
    private Collection $pageAccesses;

    public function __construct()
    {
        $this->pageAccesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

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
            $pageAccess->setPage($this);
        }

        return $this;
    }

    public function removePageAccess(PageAccess $pageAccess): static
    {
        if ($this->pageAccesses->removeElement($pageAccess)) {
            // set the owning side to null (unless already changed)
            if ($pageAccess->getPage() === $this) {
                $pageAccess->setPage(null);
            }
        }

        return $this;
    }
}
