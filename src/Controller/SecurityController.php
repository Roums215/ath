<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Déconnexion « manuelle » par GET (sans CSRF).
     * On vide le token, on invalide la session, puis on redirige.
     */
    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(Request $request, TokenStorageInterface $tokenStorage): Response
    {
        // 1) vider le token pour déconnecter immédiatement
        $tokenStorage->setToken(null);

        // 2) invalider la session pour supprimer toutes les données
        $session = $request->getSession();
        if ($session) {
            // Force la destruction de la session existante
            $session->clear();
            $session->invalidate(0);
            $session->migrate(true); // Force la création d'une nouvelle session vide
        }

        // 3) créer une réponse qui redirige vers login
        $response = $this->redirectToRoute('login');

        // 4) supprimer explicitement le cookie PHPSESSID (ceinture et bretelles)
        $response->headers->clearCookie('PHPSESSID');
        
        return $response;
    }
    
    #[Route('/debug/auth', name: 'debug_auth')]
    public function debugAuth(Request $request): Response
    {
        $user = $this->getUser();
        $session = $request->getSession();
        
        $debug = [
            'authenticated' => $user !== null,
            'user_type' => $session->get('user_type'),
            'last_username' => $session->get('_security.last_username'),
            'debug_auth_attempt' => $session->get('debug_auth_attempt'),
            'user_info' => $user ? [
                'class' => get_class($user),
                'roles' => $user->getRoles(),
                'username' => $user instanceof \App\Entity\Admin ? $user->getANom() : ($user instanceof \App\Entity\Personnel ? $user->getPNom() : 'unknown')
            ] : null,
            'session_id' => $session->getId(),
            'session_metadata' => $session->getMetadataBag()->getCreated(),
        ];
        
        return new JsonResponse($debug);
    }
}
