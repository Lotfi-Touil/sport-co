<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\InvoiceProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceProduct>
 *
 * @method InvoiceProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceProduct[]    findAll()
 * @method InvoiceProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceProduct::class);
    }

    public function findTopSellingProducts(): array
    {
        $qb = $this->createQueryBuilder('ip')
            ->select('p.id AS productId, p.name as productName, SUM(ip.quantity) as totalQuantity')
            ->join('ip.product', 'p')
            ->groupBy('p.id')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults(5)
            ->getQuery();

        return $qb->getResult();
    }

    public function findTopSellingProductsForCompany(Company $company): array
    {
        $result = $this->createQueryBuilder('ip')
            ->select('p.id AS productId, p.name as productName, SUM(ip.quantity) as totalQuantity')
            ->join('ip.product', 'p')
            ->join('ip.invoice', 'i')
            ->join('i.invoiceUsers', 'iu')
            ->join('iu.customer', 'c')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->groupBy('p.id')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults(10)
            ->getQuery();

        return $result->getResult();
    }

//    /**
//     * @return InvoiceProduct[] Returns an array of InvoiceProduct objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvoiceProduct
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
