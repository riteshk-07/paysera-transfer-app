<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DebugController
{
    private AccountRepository $repo;

    public function __construct(AccountRepository $repo)
    {
        $this->repo = $repo;
    }

    #[Route('/debug/repo', methods: ['GET'])]
    public function test(): JsonResponse
    {
        try {
            $items = $this->repo->findAll();

            return new JsonResponse([
                'ok' => true,
                'count' => count($items),
                'repo_class' => get_class($this->repo),
                'em_class' => get_class($this->repo->getEntityManager()),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'ok' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
