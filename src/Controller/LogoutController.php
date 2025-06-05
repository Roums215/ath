<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutController extends AbstractController
{
    #[Route('/deconnexion', name: 'app_deconnexion')]
    public function index(TokenStorageInterface $tokenStorage): Response
    {
        // Invalider manuellement la session
        $tokenStorage->setToken(null);
        $this->container->get('session')->invalidate();
        
        // Rediriger vers la page de login
        return $this->redirectToRoute('login');
    }
}
