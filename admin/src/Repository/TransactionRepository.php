<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    //    /**
    //     * @return Transaction[] Returns an array of Transaction objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

       public function findOneByPaymentIntentId($value): ?Transaction
       {
           return $this->createQueryBuilder('t')
               ->andWhere('t.paymentIntentId = :val')
               ->setParameter('val', $value)
               ->getQuery()
               ->getOneOrNullResult()
           ;
       }

       public function findRecentTransactions(): array
       {
           return $this->createQueryBuilder('t')
                ->addSelect('t')
                // ->join('o.orderItems','i')
                ->orderBy('t.created','DESC')
                // ->addOrderBy('w.name', 'ASC')
                // ->where('o.createdAt > :date')->setParameter('date','2023-08-01')
               ->setMaxResults(10)
               ->getQuery()
               ->getResult()
           ;
       }
              
}
