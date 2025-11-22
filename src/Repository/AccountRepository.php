<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class AccountRepository extends ServiceEntityRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function lockForUpdate(Account $account): void
    {
        // Ensure entity is managed, then lock it
        $em = $this->getEntityManager();
        if (!$em->contains($account)) {
            $account = $em->merge($account);
        }
        $em->lock($account, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
    }

    public function findOneByUuid(string $uuid): ?Account
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
