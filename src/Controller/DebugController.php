<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DebugController extends AbstractController
{
    #[Route('/debug/auth', name: 'debug_auth')]
    public function debugAuth(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        $sessionData = $request->getSession()->all();
        $token = $tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;
        
        $debug = [
            'session' => array_filter($sessionData, function($key) {
                return !in_array($key, ['_sf2_attributes', '_sf2_flashes', '_sf2_meta']);
            }, ARRAY_FILTER_USE_KEY),
            'user' => $user ? [
                'class' => get_class($user),
                'identifier' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ] : null,
            'token' => $token ? [
                'class' => get_class($token),
                'authenticated' => $token->isAuthenticated(),
            ] : null,
        ];
        
        return $this->render('debug/auth.html.twig', [
            'debug' => $debug,
        ]);
    }
}
