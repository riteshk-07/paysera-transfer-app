<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\LedgerEntryRepository')]
#[ORM\Table(name: 'ledger_entries')]
class LedgerEntry
{
    public const TYPE_DEBIT = 'DEBIT';
    public const TYPE_CREDIT = 'CREDIT';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Transfer::class)]
    #[ORM\JoinColumn(name: 'transfer_id', referencedColumnName: 'uuid', nullable: false)]
    private Transfer $transfer;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
    private Account $account;

    #[ORM\Column(type: 'string', length: 10)]
    private string $entryType;

    #[ORM\Column(type: 'bigint')]
    private int $amountCents;

    #[ORM\Column(type: 'bigint')]
    private int $balanceAfterCents;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Transfer $transfer,
        Account $account,
        string $entryType,
        int $amountCents,
        int $balanceAfterCents
    ) {
        $this->transfer = $transfer;
        $this->account = $account;
        $this->entryType = $entryType;
        $this->amountCents = $amountCents;
        $this->balanceAfterCents = $balanceAfterCents;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransfer(): Transfer
    {
        return $this->transfer;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getEntryType(): string
    {
        return $this->entryType;
    }

    public function getAmountCents(): int
    {
        return $this->amountCents;
    }

    public function getBalanceAfterCents(): int
    {
        return $this->balanceAfterCents;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
