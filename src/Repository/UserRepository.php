<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use App\Entity\Company;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAllByCompanyId($companyId): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSignupCountsByMonth()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT EXTRACT(YEAR FROM u.created_at) AS year, 
        TO_CHAR(u.created_at, \'Month\') AS month,
        COUNT(u.id) AS count
        FROM "user" u
        GROUP BY year, month
        ORDER BY year, month ASC
    ';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

    public function findSignupCountsByMonthForCompany(Company $company)
{
    $users = $this->createQueryBuilder('u')
        ->select('u.createdAt, COUNT(u.id) AS count')
        ->where('u.company = :company')
        ->setParameter('company', $company)
        ->groupBy('u.createdAt')
        ->getQuery()
        ->getResult();

    $monthlyCounts = [];
    foreach ($users as $user) {
        $year = $user['createdAt']->format('Y');
        $month = $user['createdAt']->format('m');

        if (!isset($monthlyCounts[$year][$month])) {
            $monthlyCounts[$year][$month] = 0;
        }
        $monthlyCounts[$year][$month] += $user['count'];
    }

    $formattedResults = [];
    foreach ($monthlyCounts as $year => $months) {
        foreach ($months as $month => $count) {
            $formattedResults[] = [
                'year' => $year,
                'month' => $month,
                'count' => $count,
            ];
        }
    }

    return $formattedResults;
}




    // Function that return the number of users in the database
    public function countUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findGrowthRateUsersByMonth(): int
    {
        $qb = $this->createQueryBuilder('u');

        $previousMonthCount = $qb->select('count(u.id)')
            ->where('u.createdAt BETWEEN :startPreviousMonth AND :endPreviousMonth')
            ->setParameter('startPreviousMonth', (new \DateTime('first day of last month'))->format('Y-m-d'))
            ->setParameter('endPreviousMonth', (new \DateTime('last day of last month'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        $qb = $this->createQueryBuilder('u');

        $currentMonthCount = $qb->select('count(u.id)')
            ->where('u.createdAt BETWEEN :startCurrentMonth AND :endCurrentMonth')
            ->setParameter('startCurrentMonth', (new \DateTime('first day of this month'))->format('Y-m-d'))
            ->setParameter('endCurrentMonth', (new \DateTime('now'))->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();

        if ($previousMonthCount > 0) {
            $growthRate = (($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100;
        } else {
            $growthRate = $currentMonthCount > 0 ? 100 : 0;
        }

        return (int) round($growthRate);
    }




    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
