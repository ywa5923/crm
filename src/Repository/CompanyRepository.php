<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    //    /**
    //     * @return Company[] Returns an array of Company objects
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

    //    public function findOneBySomeField($value): ?Company
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getFindAllQueryBuilder()
    {
        return $this->createQueryBuilder('c')
            ->addOrderBy('c.name', Criteria::ASC);
    }

  
    public function search($searchField, $searchTerm, $county, $city, $street, $agent)
    {
        $qb = $this->createQueryBuilder('c');

        if ($searchTerm && $searchField) {

            $qb->innerJoin('c.owner', 'u')
                ->andWhere($searchField)->setParameter('field', '%' . $searchTerm . '%');
        }


        if ($county || $city || $street) {
            $qb->innerJoin('c.workstationAddress', 'a');
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

        if ($agent)
            $qb->andWhere('c.agent = :agent')
                ->setParameter('agent', $agent);

        //dd($qb->getQuery());
        return $qb;
    }
}
