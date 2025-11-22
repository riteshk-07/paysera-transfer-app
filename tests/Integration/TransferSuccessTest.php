<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\AccountService;
use App\Service\TransferService;

class TransferSuccessTest extends KernelTestCase
{
    use DatabaseResetTrait;

    public function test_successful_transfer(): void
    {
        self::bootKernel(['environment' => 'test']);
        $this->resetDatabase();

        $container = static::getContainer();
        $accountService = $container->get(AccountService::class);
        $transferService = $container->get(TransferService::class);

        $a1 = $accountService->createAccount("Alice");
        $a2 = $accountService->createAccount("Bob");

        // Give Alice 1000 INR (1000 * 100 = 100000 paisa)
        $a1->credit(100000);
        $container->get('doctrine.orm.entity_manager')->flush();

        $transfer = $transferService->transfer(
            $a1,
            $a2,
            5050,              // 50.50 INR
            'idem-123',
            null
        );

        $this->assertEquals('COMPLETED', $transfer->getStatus());
        $this->assertEquals(94950, $a1->getBalanceCents());
        $this->assertEquals(5050, $a2->getBalanceCents());
    }
}
