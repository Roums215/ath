<?php
// src/Controller/AthController.php
namespace App\Controller;

use App\Entity\Site;
use App\Entity\Bunker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ath')]
class AthController extends AbstractController
{
    /**
     * Page plein-écran (HEAD-UP DISPLAY)
     */
    #[Route('/{id}', name: 'ath_show', requirements: ['id' => '\d+'])]
    public function show(Site $site): Response
    {
        // -> Le template récupère {{ site.id }} et {{ site.nom }}
        return $this->render('ath/show.html.twig', [
            'site' => $site,
        ]);
    }

    /**
     * End-point JSON pour les requêtes Ajax
     */
    #[Route('/{id}/data', name: 'ath_data', requirements: ['id' => '\d+'])]
    public function data(Site $site): JsonResponse
    {
        $bunkers = array_map(
            static fn (Bunker $b) => [
                'nom'  => $b->getBNom(),
                'etat' => $b->getBEtat() ?: 'Pas de retard',
            ],
            $site->getBunkers()->toArray()
        );

        return $this->json(['bunkers' => $bunkers]);
    }
}
