<?php

namespace App\Controller\Api;

use App\Service\TransferService;
use App\Service\AccountService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/transfers')]
class TransferController
{
    public function __construct(
        private AccountService $accountService,
        private TransferService $transferService
    ) {}

    #[Route('', name: 'transfer_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $fromUuid = $payload['from_account_id'] ?? null;
        $toUuid = $payload['to_account_id'] ?? null;
        $amount = $payload['amount'] ?? null;
        $metadata = $payload['metadata'] ?? null;

        if (!$fromUuid || !$toUuid || !$amount) {
            return new JsonResponse(['error' => 'Missing required fields'], 422);
        }

        $idempotencyKey = $request->headers->get('Idempotency-Key');
        if (!$idempotencyKey) {
            return new JsonResponse(['error' => 'Idempotency-Key header required'], 400);
        }

        $from = $this->accountService->getByUuid($fromUuid);
        $to = $this->accountService->getByUuid($toUuid);

        if (!$from || !$to) {
            return new JsonResponse(['error' => 'Invalid account'], 404);
        }

        try {
            $amountCents = $this->accountService->amountToCents((float)$amount);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid amount format'], 422);
        }

        if ($amountCents <= 0) {
            return new JsonResponse(['error' => 'Amount must be > 0'], 422);
        }

        try {
            $transfer = $this->transferService->transfer(
                $from, $to, $amountCents, $idempotencyKey,
                is_array($metadata) ? $metadata : null
            );
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Internal transfer error'], 500);
        }

        return new JsonResponse([
            'transfer_id' => $transfer->getUuid(),
            'status' => $transfer->getStatus(),
            'amount' => $amount,
            'currency' => $transfer->getCurrency(),
            'from_account' => $from->getUuid(),
            'to_account' => $to->getUuid()
        ], 201);
    }

    #[Route('/{uuid}', name: 'transfer_get', methods: ['GET'])]
    public function getTransfer(string $uuid): JsonResponse
    {
        $transfer = $this->transferService
            ->getRepository()
            ->find($uuid);

        if (!$transfer) {
            return new JsonResponse(['error' => 'Transfer not found'], 404);
        }

        return new JsonResponse([
            'transfer_id' => $transfer->getUuid(),
            'status' => $transfer->getStatus(),
            'amount' => $this->accountService->centsToAmount($transfer->getAmountCents()),
            'currency' => $transfer->getCurrency(),
            'from_account' => $transfer->getFromAccount()->getUuid(),
            'to_account' => $transfer->getToAccount()->getUuid(),
            'created_at' => $transfer->getCreatedAt()->format('c'),
        ], 200);
    }
}
