<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    private Company $company;

    #[ORM\Column]
    private float $totalRevenue;

    #[ORM\Column]
    private float $totalExpenses;

    #[ORM\Column]
    private float $netProfit;

    // Simplification : stockage des détails sous forme de chaîne JSON
    #[ORM\Column(type: 'text')]
    private string $paymentDetails;

    #[ORM\Column(type: 'text')]
    private string $topSellingProducts;

    #[ORM\Column]
    private int $newCustomersCount;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    public function getTotalRevenue(): float
    {
        return $this->totalRevenue;
    }

    public function setTotalRevenue(float $totalRevenue): self
    {
        $this->totalRevenue = $totalRevenue;
        return $this;
    }

    public function getTotalExpenses(): float
    {
        return $this->totalExpenses;
    }

    public function setTotalExpenses(float $totalExpenses): self
    {
        $this->totalExpenses = $totalExpenses;
        return $this;
    }

    public function getNetProfit(): float
    {
        return $this->netProfit;
    }

    public function setNetProfit(float $netProfit): self
    {
        $this->netProfit = $netProfit;
        return $this;
    }

    public function getPaymentDetails(): string
    {
        return $this->paymentDetails;
    }

    public function setPaymentDetails(string $paymentDetails): self
    {
        $this->paymentDetails = $paymentDetails;
        return $this;
    }

    public function getTopSellingProducts(): string
    {
        return $this->topSellingProducts;
    }

    public function setTopSellingProducts(string $topSellingProducts): self
    {
        $this->topSellingProducts = $topSellingProducts;
        return $this;
    }

    public function getNewCustomersCount(): int
    {
        return $this->newCustomersCount;
    }

    public function setNewCustomersCount(int $newCustomersCount): self
    {
        $this->newCustomersCount = $newCustomersCount;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
