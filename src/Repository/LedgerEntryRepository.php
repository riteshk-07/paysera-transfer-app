<?php

namespace App\Repository;

use App\Entity\LedgerEntry;
use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class LedgerEntryRepository extends ServiceEntityRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LedgerEntry::class);
    }

    /**
     * Fetch the latest ledger entry for an account, ordered by creation time.
     */
    public function getLastEntry(Account $account): ?LedgerEntry
    {
        return $this->findOneBy(
            ['account' => $account],
            ['id' => 'DESC']
        );
    }

    /**
     * Audit or debugging helper: list all entries for an account.
     */
    public function findByAccount(Account $account): array
    {
        return $this->findBy(
            ['account' => $account],
            ['id' => 'DESC']
        );
    }
}
