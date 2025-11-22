<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\AccountService;
use App\Service\TransferService;

class InsufficientFundsTest extends KernelTestCase
{
    use DatabaseResetTrait;

    public function test_transfer_fails_when_balance_too_low(): void
    {
        self::bootKernel(['environment' => 'test']);
        $this->resetDatabase();

        $container = static::getContainer();
        $accountService = $container->get(AccountService::class);
        $transferService = $container->get(TransferService::class);

        $a1 = $accountService->createAccount("Alice");
        $a2 = $accountService->createAccount("Bob");

        $transfer = $transferService->transfer(
            $a1,
            $a2,
            50000,
            'idem-999',
            null
        );

        $this->assertEquals('FAILED', $transfer->getStatus());
        $this->assertEquals(0, $a1->getBalanceCents());
        $this->assertEquals(0, $a2->getBalanceCents());
    }
}
