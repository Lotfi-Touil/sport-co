<?php

namespace App\Entity;

use App\Repository\PageAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageAccessRepository::class)]
class PageAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pageAccesses')]
    private ?User $employe = null;

    #[ORM\ManyToOne(inversedBy: 'pageAccesses')]
    private ?Page $page = null;

    #[ORM\Column(nullable: true)]
    private ?bool $canAccess = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmploye(): ?User
    {
        return $this->employe;
    }

    public function setEmploye(?User $employe): self
    {
        $this->employe = $employe;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getCanAccess(): ?bool
    {
        return $this->canAccess;
    }

    public function setCanAccess(?bool $canAccess): self
    {
        $this->canAccess = $canAccess;

        return $this;
    }
}