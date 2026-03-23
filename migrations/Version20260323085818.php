<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323085818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_atelier ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_atelier ADD CONSTRAINT FK_C86AEECF642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_C86AEECF642B8210 ON inscription_atelier (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_atelier DROP FOREIGN KEY FK_C86AEECF642B8210');
        $this->addSql('DROP INDEX IDX_C86AEECF642B8210 ON inscription_atelier');
        $this->addSql('ALTER TABLE inscription_atelier DROP admin_id');
    }
}
