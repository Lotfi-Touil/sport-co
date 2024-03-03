<?php

namespace App\Repository;

use App\Entity\PageAccess;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageAccess>
 *
 * @method PageAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageAccess[]    findAll()
 * @method PageAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageAccess::class);
    }

    /**
     * @return PageAccess[] Returns an array of PageAccess objects for a given user
     */
    public function findPermissionsByUser(User $user): array
    {
        return $this->createQueryBuilder('pa')
            ->andWhere('pa.employe = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
