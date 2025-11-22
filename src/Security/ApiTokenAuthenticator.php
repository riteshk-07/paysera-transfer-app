<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly array $allowedTokens
    ) {}

    public function supports(Request $request): ?bool
    {
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Missing or invalid Authorization header');
        }

        $token = trim(substr($authHeader, 7));

        if (!in_array($token, $this->allowedTokens, true)) {
            throw new AuthenticationException('Invalid API token');
        }
        
        return new SelfValidatingPassport(
            new UserBadge(
                $token,
                fn (string $userIdentifier) =>
                    new InMemoryUser($userIdentifier, null, ['ROLE_API'])
            )
        );

    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => 'Authentication failed',
            'message' => $exception->getMessage(),
        ], Response::HTTP_UNAUTHORIZED);
    }
}
