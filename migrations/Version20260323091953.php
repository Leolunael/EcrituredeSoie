<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323091953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_visio ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_visio ADD CONSTRAINT FK_9B527498642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_9B527498642B8210 ON inscription_visio (admin_id)');
        $this->addSql('ALTER TABLE inscription_vollon ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_vollon ADD CONSTRAINT FK_7B69A52642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_7B69A52642B8210 ON inscription_vollon (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_visio DROP FOREIGN KEY FK_9B527498642B8210');
        $this->addSql('DROP INDEX IDX_9B527498642B8210 ON inscription_visio');
        $this->addSql('ALTER TABLE inscription_visio DROP admin_id');
        $this->addSql('ALTER TABLE inscription_vollon DROP FOREIGN KEY FK_7B69A52642B8210');
        $this->addSql('DROP INDEX IDX_7B69A52642B8210 ON inscription_vollon');
        $this->addSql('ALTER TABLE inscription_vollon DROP admin_id');
    }
}
