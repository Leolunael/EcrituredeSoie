<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428123434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lettre ADD a_la_une TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE visio ADD a_la_une TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE vollon ADD a_la_une TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lettre DROP a_la_une');
        $this->addSql('ALTER TABLE visio DROP a_la_une');
        $this->addSql('ALTER TABLE vollon DROP a_la_une');
    }
}
