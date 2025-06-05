<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        // Cette méthode sera interceptée par le firewall, mais au cas où
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
