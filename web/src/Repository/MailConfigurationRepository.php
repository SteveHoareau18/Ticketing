<?php

namespace App\Repository;

use App\Entity\MailConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MailConfiguration>
 *
 * @method MailConfiguration|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailConfiguration|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailConfiguration[]    findAll()
 * @method MailConfiguration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailConfiguration::class);
    }

//    /**
//     * @return MailConfiguration[] Returns an array of MailConfiguration objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MailConfiguration
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
