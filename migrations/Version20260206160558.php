<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206160558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inscription_vollon (id INT AUTO_INCREMENT NOT NULL, vollon_id INT NOT NULL, user_id INT DEFAULT NULL, user_type VARCHAR(50) DEFAULT NULL, external_user_id VARCHAR(255) DEFAULT NULL, name VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, date_inscription DATETIME NOT NULL, commentaire LONGTEXT DEFAULT NULL, moyen_paiement VARCHAR(20) NOT NULL, INDEX IDX_7B69A526C089D8 (vollon_id), INDEX IDX_7B69A52A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vollon (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, date_vollon DATE DEFAULT NULL, heure_debut TIME DEFAULT NULL, heure_fin TIME DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, adresse VARCHAR(500) DEFAULT NULL, is_archive TINYINT(1) DEFAULT 0 NOT NULL, image VARCHAR(500) DEFAULT NULL, places_max INT DEFAULT NULL, informations LONGTEXT DEFAULT NULL, lien_hello_asso VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscription_vollon ADD CONSTRAINT FK_7B69A526C089D8 FOREIGN KEY (vollon_id) REFERENCES vollon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_vollon ADD CONSTRAINT FK_7B69A52A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_vollon DROP FOREIGN KEY FK_7B69A526C089D8');
        $this->addSql('ALTER TABLE inscription_vollon DROP FOREIGN KEY FK_7B69A52A76ED395');
        $this->addSql('DROP TABLE inscription_vollon');
        $this->addSql('DROP TABLE vollon');
    }
}
