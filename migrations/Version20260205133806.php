<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205133806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visio CHANGE date_visio date_visio DATE DEFAULT NULL, CHANGE heure_fin heure_fin TIME DEFAULT NULL, CHANGE heure_debut heure_debut TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visio CHANGE date_visio date_visio DATETIME NOT NULL, CHANGE heure_debut heure_debut DATETIME DEFAULT NULL, CHANGE heure_fin heure_fin DATETIME DEFAULT NULL');
    }
}
