<?php

namespace App\Service;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;

class AccountService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AccountRepository $repo,
        private string $currency,
        private int $precision
    ) {}

    public function createAccount(string $ownerName): Account
    {
        $account = new Account($ownerName, $this->currency);
        $this->em->persist($account);
        $this->em->flush();
        return $account;
    }

    public function getByUuid(string $uuid): ?Account
    {
        return $this->repo->findOneByUuid($uuid);
    }

    public function amountToCents(float $amount): int
    {
        return (int) round($amount * (10 ** $this->precision));
    }

    public function centsToAmount(int $cents): float
    {
        return $cents / (10 ** $this->precision);
    }
}
