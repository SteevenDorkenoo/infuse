<?php

namespace App\Repository;

use App\Entity\Endorsement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Endorsement>
 *
 * @method Endorsement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Endorsement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Endorsement[]    findAll()
 * @method Endorsement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EndorsementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Endorsement::class);
    }

//    /**
//     * @return Endorsement[] Returns an array of Endorsement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Endorsement
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
