<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Entity\Bunker;
use App\Entity\Personnel;
use App\Entity\Admin;
use App\Entity\Logs;
// Ne pas utiliser AsDashboard pour le moment
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Redirection vers la liste des sites
        $url = $this->container
            ->get(AdminUrlGenerator::class)
            ->setController(SiteCrudController::class)
            ->generateUrl();
            
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Radiothérapie • Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        /* ─── Cliniques ───────────────────────────────────── */
        yield MenuItem::section('Clinique');
        yield MenuItem::linkToCrud('Sites', 'fa fa-hospital', Site::class);

        /* ─── Infrastructures ─────────────────────────────── */
        yield MenuItem::section('Infrastructures');
        yield MenuItem::linkToCrud('Bunkers', 'fa fa-microchip', Bunker::class);

        /* ─── Utilisateurs ───────────────────────────────── */
        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Personnels', 'fa fa-user-nurse', Personnel::class);
        yield MenuItem::linkToCrud('Admins', 'fa fa-user-shield', Admin::class)
            ->setPermission('ROLE_ADMIN'); // optionnel

        /* ─── Logs ───────────────────────────────────────── */
        yield MenuItem::section('Logs');
        yield MenuItem::linkToCrud('Historique', 'fa fa-list', Logs::class);
        
        /* ─── Compte ───────────────────────────────────────── */
        yield MenuItem::section('Compte');
        yield MenuItem::linkToUrl('Déconnexion', 'fa fa-sign-out', '/deconnexion');
    }
}
