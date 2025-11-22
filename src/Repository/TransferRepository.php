<?php

namespace App\Repository;

use App\Entity\Transfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class TransferRepository extends ServiceEntityRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfer::class);
    }

    public function findByIdempotencyKey(string $key): ?Transfer
    {
        return $this->findOneBy(['idempotencyKey' => $key]);
    }
}
