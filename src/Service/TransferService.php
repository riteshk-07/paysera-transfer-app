<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Transfer;
use App\Entity\LedgerEntry;
use App\Repository\AccountRepository;
use App\Repository\LedgerEntryRepository;
use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client as Redis;

class TransferService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TransferRepository $transferRepo,
        private AccountRepository $accountRepo,
        private LedgerEntryRepository $ledgerRepo,
        private Redis $redis, // Reserved for future caching/idempotency optimization
        private string $currency,
        private int $precision
    ) {}

    public function transfer(
        Account $from,
        Account $to,
        int $amountCents,
        string $idempotencyKey,
        ?array $metadata = null
    ): Transfer {
        $existing = $this->transferRepo->findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return $existing;
        }

        $transfer = new Transfer(
            fromAccount: $from,
            toAccount: $to,
            amountCents: $amountCents,
            currency: $this->currency,
            idempotencyKey: $idempotencyKey,
            metadata: $metadata
        );

        $this->em->beginTransaction();

        try {
            // Fetch accounts fresh within transaction to ensure they are managed entities
            // This is critical for pessimistic locking to work correctly
            $fromId = $from->getId();
            $toId = $to->getId();
            $from = $this->accountRepo->find($fromId);
            $to = $this->accountRepo->find($toId);
            
            if (!$from || !$to) {
                throw new \RuntimeException('Accounts not found');
            }
            
            // Lock accounts in consistent order to prevent deadlocks
            $this->lockAccounts($from, $to);

            if ($from->getBalanceCents() < $amountCents) {
                $transfer->markFailed();
                $this->em->persist($transfer);
                $this->em->flush();
                $this->em->commit();
                return $transfer;
            }

            $transfer->markPending();
            $this->em->persist($transfer);

            $from->debit($amountCents);
            $to->credit($amountCents);

            $this->em->persist($from);
            $this->em->persist($to);

            $fromEntry = new LedgerEntry(
                $transfer, $from, LedgerEntry::TYPE_DEBIT,
                -$amountCents,
                $from->getBalanceCents()
            );

            $toEntry = new LedgerEntry(
                $transfer, $to, LedgerEntry::TYPE_CREDIT,
                $amountCents,
                $to->getBalanceCents()
            );

            $this->em->persist($fromEntry);
            $this->em->persist($toEntry);

            $transfer->markCompleted();
            $this->em->flush();
            $this->em->commit();

            return $transfer;

        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function lockAccounts(Account $a, Account $b): void
    {
        if ($a->getId() < $b->getId()) {
            $this->accountRepo->lockForUpdate($a);
            $this->accountRepo->lockForUpdate($b);
        } else {
            $this->accountRepo->lockForUpdate($b);
            $this->accountRepo->lockForUpdate($a);
        }
    }

    public function getRepository(): TransferRepository
    {
        return $this->transferRepo;
    }
}
