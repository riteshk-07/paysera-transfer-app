<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\AccountService;
use App\Service\TransferService;

class ConcurrencyTest extends KernelTestCase
{
    use DatabaseResetTrait;

    public function test_multiple_rapid_transfers_do_not_overdraw(): void
    {
        self::bootKernel(['environment' => 'test']);
        $this->resetDatabase();

        $container = static::getContainer();
        $accountService = $container->get(AccountService::class);
        $transferService = $container->get(TransferService::class);

        $a1 = $accountService->createAccount("Alice");
        $a2 = $accountService->createAccount("Bob");

        $a1->credit(10000); // 100 INR
        $container->get('doctrine.orm.entity_manager')->flush();

        // First transfer succeeds
        $t1 = $transferService->transfer($a1, $a2, 6000, 'idem-1', []);

        // Second transfer tries to pull more than remaining
        $t2 = $transferService->transfer($a1, $a2, 6000, 'idem-2', []);

        $this->assertEquals('COMPLETED', $t1->getStatus());
        $this->assertEquals('FAILED', $t2->getStatus());

        $this->assertEquals(4000, $a1->getBalanceCents());
        $this->assertEquals(6000, $a2->getBalanceCents());
    }
}
