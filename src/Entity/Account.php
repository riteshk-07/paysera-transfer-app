<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: 'App\Repository\AccountRepository')]
#[ORM\Table(name: 'accounts')]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $ownerName;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'bigint')]
    private int $balanceCents;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function __construct(string $ownerName, string $currency = 'INR')
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->ownerName = $ownerName;
        $this->currency = $currency;
        $this->balanceCents = 0;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getBalanceCents(): int
    {
        return $this->balanceCents;
    }

    public function credit(int $amountCents): void
    {
        // No validation here. TransferService handles rules.
        $this->balanceCents += $amountCents;
        $this->updatedAt = new \DateTime();
    }

    public function debit(int $amountCents): void
    {
        $this->balanceCents -= $amountCents;
        $this->updatedAt = new \DateTime();
    }
}
