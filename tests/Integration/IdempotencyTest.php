<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\AccountService;
use App\Service\TransferService;

class IdempotencyTest extends KernelTestCase
{
    use DatabaseResetTrait;

    public function test_idempotent_transfer(): void
    {
        self::bootKernel(['environment' => 'test']);
        $this->resetDatabase();

        $container = static::getContainer();
        $accountService = $container->get(AccountService::class);
        $transferService = $container->get(TransferService::class);

        $a1 = $accountService->createAccount("Alice");
        $a2 = $accountService->createAccount("Bob");

        $a1->credit(100000);
        $container->get('doctrine.orm.entity_manager')->flush();

        $t1 = $transferService->transfer($a1, $a2, 2000, 'idem-abc', []);
        $t2 = $transferService->transfer($a1, $a2, 2000, 'idem-abc', []);

        $this->assertSame($t1->getUuid(), $t2->getUuid());
        $this->assertEquals(98000, $a1->getBalanceCents());
        $this->assertEquals(2000, $a2->getBalanceCents());
    }
}
