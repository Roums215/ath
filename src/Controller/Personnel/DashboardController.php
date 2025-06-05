<?php

namespace App\Controller\Personnel;

use App\Entity\Bunker;
use App\Entity\Personnel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/personnel', name: 'personnel_dashboard_')]
#[IsGranted('ROLE_PERSONNEL')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var Personnel $user */
        $user = $this->getUser();
        $site = $user->getSite();
        
        if (!$site) {
            $this->addFlash('error', 'Vous n\'êtes rattaché à aucun site.');
        }
        
        // Récupérer les bunkers du site de l'utilisateur connecté
        $bunkers = [];
        if ($site) {
            $bunkers = $entityManager->getRepository(Bunker::class)
                ->findBy(['site' => $site]);
        }
        
        return $this->render('personnel/dashboard.html.twig', [
            'user' => $user,
            'site' => $site,
            'bunkers' => $bunkers
        ]);
    }
}
