<?php

namespace App\Repository;

use App\Entity\Donation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Donation>
 */
class DonationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    //    /**
    //     * @return Donation[] Returns an array of Donation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Donation
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findOneByPaymentIntentId($value): ?Donation
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.paymentIntentId = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }  

    public function paymentIntentIdExists($value)
    {
        return (boolean)$this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.paymentIntentId = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR) > 0;
    }  

    public function findRecentDonations(): array
    {
        return $this->createQueryBuilder('d')
             ->addSelect('d')
             ->orderBy('d.created','DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
 
     public function getDonationTotals()
     {
         return $this->createQueryBuilder('d')
             ->select('sum(d.amount) donationTotal, count(d.id) as donationCount')
             ->getQuery()
             ->getOneOrNullResult();
     }  
 
     public function getDonationTotalsByYear()
     {
         return $this->createQueryBuilder('d')
             ->select('sum(d.amount) donationTotal, count(d.id) as donationCount, d.filingYear')
             ->groupBy('d.filingYear')
             ->orderBy('d.filingYear','DESC')
             ->getQuery()
             ->getResult();
     }  

}
