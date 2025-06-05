<?php
// src/Controller/Personnel/SimpleAjaxController.php
namespace App\Controller\Personnel;

use App\Entity\Bunker;
use App\Entity\Personnel;
use App\Entity\Logs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour les actions AJAX du personnel - SANS VÉRIFICATION DE RÔLE
 */
#[Route('/personnel')]
#[IsGranted('ROLE_PERSONNEL')]
class SimpleAjaxController extends AbstractController
{
    /**
     * Liste des bunkers pour le site du personnel
     */
    #[Route('/api/bunkers', name: 'personnel_api_bunkers', methods: ['GET'])]
    public function listBunkers(EntityManagerInterface $em): JsonResponse
    {
        /** @var Personnel $user */
        $user = $this->getUser();
        $site = $user->getSite();
        
        if (!$site) {
            return $this->json(['error' => 'Utilisateur non rattaché à un site'], 400);
        }
        
        $bunkers = $em->getRepository(Bunker::class)->findBy(['site' => $site]);
        
        $data = [];
        foreach ($bunkers as $bunker) {
            $data[] = [
                'Id_Bunker' => $bunker->getId(),
                'BNom' => $bunker->getBNom(),
                'BEtat' => $bunker->getBEtat() ?: 'Pas de retard'
            ];
        }
        
        return $this->json($data);
    }
    
    /**
     * Mise à jour de l'état d'un bunker
     */
    #[Route('/api/update-bunker', name: 'personnel_api_update_bunker', methods: ['POST'])]
    public function updateBunker(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var Personnel $user */
        $user = $this->getUser();
        $site = $user->getSite();

        // — 1. Récupérer les paramètres envoyés ------------------------------
        $idBunker = (int) $request->request->get('id');     // ex :  7
        $nouvelEtat = $request->request->get('etat');       // ex : "Retard 10 min"

        if (!$idBunker || !$nouvelEtat) {
            return $this->json(['success'=>false,'message'=>'Paramètres manquants'], 400);
        }

        // — 2. Aller chercher le bunker et vérifier qu'il appartient au site --
        $bunkerRepo = $em->getRepository(Bunker::class);
        /** @var Bunker|null $bunker */
        $bunker = $bunkerRepo->find($idBunker);

        if (!$bunker || $bunker->getSite() !== $site) {
            return $this->json(['success'=>false,'message'=>'Bunker introuvable'], 404);
        }

        // — 3. Mettre à jour ---------------------------------------------------
        $bunker->setBEtat($nouvelEtat);
        $bunker->setBDerniereModifPar($user);
        $em->flush();     // ⬅️ l'UPDATE part en base ici

        // Journaliser l'action
        $log = new Logs();
        $log->setLAction(sprintf('Bunker %s mis à %s', $bunker->getBNom(), $nouvelEtat));
        $log->setIdPersonnel($user);
        $em->persist($log);
        $em->flush();

        return $this->json(['success'=>true]);
    }
}
