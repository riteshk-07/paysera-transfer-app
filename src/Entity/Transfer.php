<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: 'App\Repository\TransferRepository')]
#[ORM\Table(name: 'transfers')]
class Transfer
{
    public const STATUS_CREATED = 'CREATED';
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'from_account_id', referencedColumnName: 'id', nullable: false)]
    private Account $fromAccount;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'to_account_id', referencedColumnName: 'id', nullable: false)]
    private Account $toAccount;

    #[ORM\Column(type: 'bigint')]
    private int $amountCents;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $idempotencyKey;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $completedAt;

    public function __construct(
        Account $fromAccount,
        Account $toAccount,
        int $amountCents,
        string $currency,
        string $idempotencyKey,
        ?array $metadata = null
    ) {
        $this->uuid = Uuid::uuid4()->toString();
        $this->fromAccount = $fromAccount;
        $this->toAccount = $toAccount;
        $this->amountCents = $amountCents;
        $this->currency = $currency;
        $this->idempotencyKey = $idempotencyKey;
        $this->metadata = $metadata;

        $this->status = self::STATUS_CREATED;
        $this->createdAt = new \DateTimeImmutable();
        $this->completedAt = null;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getFromAccount(): Account
    {
        return $this->fromAccount;
    }

    public function getToAccount(): Account
    {
        return $this->toAccount;
    }

    public function getAmountCents(): int
    {
        return $this->amountCents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markPending(): void
    {
        $this->status = self::STATUS_PENDING;
    }

    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = new \DateTime();
    }

    public function markFailed(): void
    {
        $this->status = self::STATUS_FAILED;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
