<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129130843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE intro (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) DEFAULT NULL, contenu LONGTEXT NOT NULL, image VARCHAR(255) NOT NULL, image_position VARCHAR(255) NOT NULL, actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE presentation ADD sous_titre VARCHAR(255) DEFAULT NULL, CHANGE titre titre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_permanent TINYINT(1) DEFAULT 0 NOT NULL, ADD mongodb_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE visio ADD places_max INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE intro');
        $this->addSql('ALTER TABLE presentation DROP sous_titre, CHANGE titre titre VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user DROP is_permanent, DROP mongodb_id');
        $this->addSql('ALTER TABLE visio DROP places_max');
    }
}
