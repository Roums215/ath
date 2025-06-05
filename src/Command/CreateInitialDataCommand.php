<?php

namespace App\Command;

use App\Entity\Admin;
use App\Entity\Site;
use App\Entity\Bunker;
use App\Entity\Personnel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-initial-data',
    description: 'Créer les données initiales pour l\'application (Admin, Site, Bunker, Personnel)',
)]
class CreateInitialDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Création des données initiales');

        // Vérifier précisément les données existantes dans chaque entité
        $adminRepo = $this->entityManager->getRepository(Admin::class);
        $siteRepo = $this->entityManager->getRepository(Site::class);
        $bunkerRepo = $this->entityManager->getRepository(Bunker::class);
        $personnelRepo = $this->entityManager->getRepository(Personnel::class);
        
        // Vérification seulement pour site et personnel
        $siteExists = $siteRepo->count(['nom' => 'Site Principal']) > 0;
        // Vérification pour les deux noms possibles de personnel pour la compatibilité
        $personnelExists = $personnelRepo->count(['pNom' => 'Doe']) > 0 || $personnelRepo->count(['pNom' => 'personnel']) > 0;
        
        // Pas de vérification pour l'admin qui sera toujours mis à jour
        
        if ($siteExists && $personnelExists) {
            $io->note('Site et Personnel existants. Seul le compte Admin sera forcé.');
            // On continue pour recréer l'admin
        }
        
        $io->note('Certaines données initiales manquantes. Création des données nécessaires uniquement.');

        try {
            // Variables pour stocker les entités créées ou récupérées
            $admin = null;
            $site = null;
            
            // 1. Mettre à jour ou créer le compte Admin
            $io->section('Mise à jour du compte Admin');
        
            // Recherche d'un Admin existant
            $admin = $adminRepo->findOneBy(['anom' => 'Admin']);
        
            if ($admin) {
                $io->note('Admin existant trouvé - mise à jour de ses informations');
                // Mise à jour de l'Admin existant
                $admin->setAPrenom('Système')
                      ->setRoles(['ROLE_ADMIN']);
                  
                // Si la date de création est null, on la définit
                if (!$admin->getACreation()) {
                    $admin->setACreation(new \DateTime());
                }
            } else {
                $io->note('Création d\'un nouveau compte Admin');
                // Création d'un nouvel admin
                $admin = new Admin();
                $admin->setANom('Admin')
                      ->setAPrenom('Système')
                      ->setRoles(['ROLE_ADMIN'])
                      ->setACreation(new \DateTime());
            
                // Persister le nouvel admin
                $this->entityManager->persist($admin);
            }
        
            // Toujours mettre à jour le mot de passe avec Admin123!
            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'Admin123!');
            $admin->setAMdp($hashedPassword);
            $io->success('Compte Admin mis à jour avec identifiants: Admin/Admin123!');
            

            // 2. Création ou récupération d'un site
            if (!$siteExists) {
                $io->section('Création du site principal');
                $site = new Site();
                $site->setNom('Site Principal')
                     ->setImagePath('site_principal.jpg');
                
                // Associer l'admin au site
                $site->setIdAdmin($admin);
                
                $this->entityManager->persist($site);
                $io->success('Site principal créé');
            } else {
                $site = $siteRepo->findOneBy(['nom' => 'Site Principal']);
                $io->note('Utilisation du site principal existant');
            }

            // 3. Création d'un bunker attaché au site s'il n'existe pas déjà
            $bunkerExists = $bunkerRepo->count(['nom' => 'Bunker 1', 'site' => $site]) > 0;
            
            if (!$bunkerExists) {
                $io->section('Création du bunker');
                $bunker = new Bunker();
                $bunker->setSite($site)
                       ->setNom('Bunker 1')
                       ->setBenum('BUN001');
                
                $this->entityManager->persist($bunker);
                $io->success('Bunker créé');
            } else {
                $io->note('Le bunker existe déjà pour ce site');
            }

            // 4. Création d'un personnel s'il n'existe pas déjà
            if (!$personnelExists) {
                $io->section('Création du compte Personnel');
                $personnel = new Personnel();
                $personnel->setPNom('Doe')
                          ->setPPrenom('John')
                          ->setRoles(['ROLE_PERSONNEL'])
                          ->setSite($site);
                
                // Utilisation du mot de passe Personnel123! comme dans la migration
                $hashedPassword = $this->passwordHasher->hashPassword($personnel, 'Personnel123!');
                $personnel->setPassword($hashedPassword);
                
                $this->entityManager->persist($personnel);
                $io->success('Compte Personnel créé avec identifiants: Doe/Personnel123!');
            } else {
                // Vérifier que le personnel existant a le bon nom et le renommer si besoin
                $personnel = $personnelRepo->findOneBy(['pNom' => 'Doe']);
                if (!$personnel) {
                    $personnel = $personnelRepo->findOneBy(['pNom' => 'personnel']);
                    if ($personnel) {
                        $io->note('Renommage du compte Personnel de "personnel" à "Doe"');
                        $personnel->setPNom('Doe');
                        $this->entityManager->flush();
                    }
                }
                // Vérification que $personnel n'est pas null avant d'utiliser ses méthodes
                if ($personnel) {
                    $io->note('Compte Personnel existant: ' . $personnel->getPNom());
                } else {
                    // Si aucun personnel existant n'a été trouvé
                    $io->warning('Aucun compte Personnel existant trouvé avec les noms standards.');
                }
            }

            // Enregistrement de toutes les entités
            $this->entityManager->flush();
            
            $io->success('Toutes les données initiales ont été créées avec succès !');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Une erreur s\'est produite : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
