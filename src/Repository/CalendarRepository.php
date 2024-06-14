<?php

namespace App\Repository;

use App\Entity\Calendar;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Calendar>
 *
 * @method Calendar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Calendar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Calendar[]    findAll()
 * @method Calendar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CalendarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Calendar::class);
    }

    //    /**
    //     * @return Calendar[] Returns an array of Calendar objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Calendar
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findIntervalData($start, $end, $user)
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.start >= :start')
            ->setParameter('start', $start)
            ->andWhere('c.end <= :end')
            ->setParameter('end', $end)
            ->andWhere('c.user = :user')
            ->setParameter('user', $user);


        return $qb->getQuery()
            ->getResult();
    }

    public function search($start, $end, $agent, $completed)
    {
        $qb = $this->createQueryBuilder('c');
        if ($start) {
            $qb->andWhere('c.start >= :start')
                ->setParameter('start', $start);
        }

        if ($end) {

            $qb->andWhere('c.start <= :end')
                ->setParameter('end', $end);
        }


        if ($agent) {
            $qb->addSelect('u')->innerJoin('c.user', 'u')
                ->andWhere('u.firstName like :user OR u.lastName like :user')
                ->setParameter('user', '%' . $agent . '%');
        }
        if ($completed) {
            $qb->andWhere('c.isComplete = :completed')
                ->setParameter('completed', true);
        }

        return $qb;
    }

    public function search2($start, $end, $agent, $client, $county, $city, $street, $completed)
    {

        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.client', 'client')->innerJoin('client.workstationAddress', 'a');
        if ($start) {
            $qb->andWhere('c.start >= :start')
                ->setParameter('start', $start);
        }

        if ($end) {

            $qb->andWhere('c.start <= :end')
                ->setParameter('end', $end);
        }


        if ($agent) {
            $qb->andWhere('c.user = :user')
                ->setParameter('user',  $agent);
        }
        if ($client) {
            $qb->andWhere('c.client = :client')
                ->setParameter('client',  $client);
        }

        if ($completed) {
            $qb->andWhere('c.isComplete = :completed')
                ->setParameter('completed', true);
        }
        if ($county) {
            $qb->andWhere('a.county LIKE :county')
                ->setParameter('county', '%' . $county . '%');
        }
        if ($city) {
            $qb->andWhere('a.city LIKE :city')
                ->setParameter('city', '%' . $city . '%');
        }
        if ($street) {
            $qb->andWhere('a.street LIKE :street')
                ->setParameter('street', '%' . $street . '%');
        }
        return $qb;
    }

    public function getFindAllQueryBuilder(?User $user = null)
    {
        $qb = $this->createQueryBuilder('c')
            ->addOrderBy('c.end', Criteria::DESC);

        if (is_null($user)) {
            return $qb;
        } else {
            return $qb->andWhere("c.user = :user")->setParameter('user', $user);
        }
    }

    public function getCompanyVisitsQB($companyId)
    {
        return $this->createQueryBuilder('c')
            ->addOrderBy('c.end', Criteria::DESC)
            ->andWhere("c.client = :client")->setParameter('client', $companyId);
    }
}
