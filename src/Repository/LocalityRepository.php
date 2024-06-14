<?php

namespace App\Repository;

use App\Entity\Locality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Localities>
 *
 * @method Localities|null find($id, $lockMode = null, $lockVersion = null)
 * @method Localities|null findOneBy(array $criteria, array $orderBy = null)
 * @method Localities[]    findAll()
 * @method Localities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocalityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locality::class);
    }

    //    /**
    //     * @return Localities[] Returns an array of Localities objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Localities
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getCounties()
    {
        return $this->createQueryBuilder('l')

            //->addGroupBy('l.county')
            ->getQuery()
            ->getResult();
    }

    public function search($searchTerm, $relatedTerm)
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.name LIKE :search')
            ->setParameter(':search', "%" . $searchTerm . "%");
        if (strlen($relatedTerm) <= 2) {
            $qb->andWhere('l.auto = :value')
                ->setParameter(':value', $relatedTerm);
        }
        if (strlen($relatedTerm) > 2) {
            $qb->andWhere('l.county = :value')
                ->setParameter(':value', $relatedTerm);
        }


        return  $qb->getQuery()
            ->getResult();
    }
}
