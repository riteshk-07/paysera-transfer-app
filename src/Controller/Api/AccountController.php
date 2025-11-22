<?php

namespace App\Controller\Api;

use App\Service\AccountService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/accounts')]
class AccountController
{
    public function __construct(
        private AccountService $accountService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'account_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['owner_name']) || empty($payload['owner_name'])) {
            return new JsonResponse(['error' => 'owner_name required'], 422);
        }

        $account = $this->accountService->createAccount($payload['owner_name']);

        return new JsonResponse([
            'id' => $account->getUuid(),
            'owner_name' => $account->getOwnerName(),
            'currency' => $account->getCurrency(),
            'balance' => $this->accountService->centsToAmount($account->getBalanceCents())
        ], 201);
    }

    #[Route('/{uuid}', name: 'account_get', methods: ['GET'])]
    public function getAccount(string $uuid): JsonResponse
    {
        $account = $this->accountService->getByUuid($uuid);

        if (!$account) {
            return new JsonResponse(['error' => 'Account not found'], 404);
        }

        return new JsonResponse([
            'id' => $account->getUuid(),
            'owner_name' => $account->getOwnerName(),
            'currency' => $account->getCurrency(),
            'balance' => $this->accountService->centsToAmount($account->getBalanceCents())
        ], 200);
    }

    #[Route('/{uuid}/transactions', name: 'account_transactions', methods: ['GET'])]
    public function getTransactions(string $uuid): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Not implemented in assignment scope'
        ], 501);
    }
}
