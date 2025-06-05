<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250604125138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insertion des données initiales (admin, site, bunker, personnel)';
    }

    public function up(Schema $schema): void
    {
        // 1) Insertion d'un Admin par défaut avec ROLE_ADMIN
        // Mot de passe : Admin123! (hashé avec bcrypt)
        $this->addSql("
            INSERT INTO `admin` (ANom, APrenom, AMdp, roles) VALUES
            ('Admin', 
             'Système', 
             '\$2y\$13\$66s7fNpJPf2aqWObKA8IT.dalQROsgaADTJzSF52DyqgYRd.Ttxv6',
             '[\"ROLE_ADMIN\"]'
            );
        ");

        // 2) Insertion d'un Site par défaut
        $this->addSql("
            INSERT INTO site (SNom, SLieu) VALUES
            ('Site Principal', 'Paris Centre');
        ");

        // 3) Association du site à l'admin (mise à jour de la relation)
        $this->addSql("
            UPDATE site SET Id_Admin = (SELECT Id_Admin FROM `admin` WHERE ANom = 'Admin' LIMIT 1)
            WHERE SNom = 'Site Principal';
        ");

        // 4) Insertion d'un Bunker par défaut rattaché au site
        $this->addSql("
            INSERT INTO bunker (BNom, BEnum, BEtat, Id_Site) VALUES
            ('Bunker Alpha', 'B001', 'Pas de retard', (SELECT Id_Site FROM site WHERE SNom = 'Site Principal' LIMIT 1));
        ");

        // 5) Insertion d'un Personnel par défaut avec ROLE_PERSONNEL rattaché au site
        // Mot de passe : Personnel123! (hashé avec bcrypt)
        $this->addSql("
            INSERT INTO personnel (PNom, PPrenom, PMdp, roles, Id_Site) VALUES
            ('Doe', 
             'John', 
             '\$2y\$13\$xgQrg9wuydxdWVU0BapJ1uUruzOJI4EbQ1XlGPZcAJzu73.cb821e',
             '[\"ROLE_PERSONNEL\"]',
             (SELECT Id_Site FROM site WHERE SNom = 'Site Principal' LIMIT 1)
            );
        ");
    }

    public function down(Schema $schema): void
    {
        // Suppression des données insérées (dans l'ordre inverse pour respecter les contraintes FK)
        $this->addSql("DELETE FROM personnel WHERE PNom = 'Doe' AND PPrenom = 'John'");
        $this->addSql("DELETE FROM bunker WHERE BNom = 'Bunker Alpha'");
        $this->addSql("DELETE FROM site WHERE SNom = 'Site Principal'");
        $this->addSql("DELETE FROM `admin` WHERE ANom = 'Admin' AND APrenom = 'Système'");
    }
}
