<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppAuthFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            // Vous pouvez personnaliser cette partie selon vos besoins
            'message' => 'Erreur d\'authentification: ' . $exception->getMessageKey()
        ];

        return new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED);
    }
}