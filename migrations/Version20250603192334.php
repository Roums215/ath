<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603192334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE `admin` (Id_Admin INT AUTO_INCREMENT NOT NULL, ANom VARCHAR(50) NOT NULL, APrenom VARCHAR(50) NOT NULL, AMdp VARCHAR(255) NOT NULL, ACreation DATETIME DEFAULT CURRENT_TIMESTAMP, roles JSON NOT NULL, PRIMARY KEY(Id_Admin)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bunker (Id_Bunker INT AUTO_INCREMENT NOT NULL, BNom VARCHAR(50) NOT NULL, BEnum VARCHAR(50) DEFAULT NULL, BEtat VARCHAR(255) DEFAULT 'Pas de retard' NOT NULL, BCreation DATETIME DEFAULT CURRENT_TIMESTAMP, Id_Site INT NOT NULL, BDerniereModifPar INT DEFAULT NULL, INDEX Id_Site (Id_Site), INDEX BDerniereModifPar (BDerniereModifPar), PRIMARY KEY(Id_Bunker)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE logs (Id_Logs INT AUTO_INCREMENT NOT NULL, LAction VARCHAR(255) NOT NULL, LDate DATETIME DEFAULT CURRENT_TIMESTAMP, Id_Personnel INT DEFAULT NULL, INDEX Id_Personnel (Id_Personnel), PRIMARY KEY(Id_Logs)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE personnel (Id_Personnel INT AUTO_INCREMENT NOT NULL, PNom VARCHAR(50) NOT NULL, PPrenom VARCHAR(50) NOT NULL, PMdp VARCHAR(255) NOT NULL, roles JSON NOT NULL, Id_Site INT NOT NULL, INDEX Id_Site (Id_Site), PRIMARY KEY(Id_Personnel)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE site (Id_Site INT AUTO_INCREMENT NOT NULL, SNom VARCHAR(50) NOT NULL, SLieu VARCHAR(50) DEFAULT NULL, Id_Admin INT DEFAULT NULL, INDEX Id_Admin (Id_Admin), PRIMARY KEY(Id_Site)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bunker ADD CONSTRAINT FK_FC364B4E8DEC8344 FOREIGN KEY (Id_Site) REFERENCES site (Id_Site) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bunker ADD CONSTRAINT FK_FC364B4E3C7A66E9 FOREIGN KEY (BDerniereModifPar) REFERENCES personnel (Id_Personnel)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE logs ADD CONSTRAINT FK_F08FC65CBC4916A2 FOREIGN KEY (Id_Personnel) REFERENCES personnel (Id_Personnel)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DE8DEC8344 FOREIGN KEY (Id_Site) REFERENCES site (Id_Site) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE site ADD CONSTRAINT FK_694309E45E3C0114 FOREIGN KEY (Id_Admin) REFERENCES `admin` (Id_Admin) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bunker DROP FOREIGN KEY FK_FC364B4E8DEC8344
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bunker DROP FOREIGN KEY FK_FC364B4E3C7A66E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE logs DROP FOREIGN KEY FK_F08FC65CBC4916A2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DE8DEC8344
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE site DROP FOREIGN KEY FK_694309E45E3C0114
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `admin`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bunker
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE logs
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE personnel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE site
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
