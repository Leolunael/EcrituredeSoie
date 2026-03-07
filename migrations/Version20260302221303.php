<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302221303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_lettre ADD lettre_id INT DEFAULT NULL, ADD adresse_postale VARCHAR(255) DEFAULT NULL, ADD date_inscription DATETIME DEFAULT NULL, ADD moyen_paiement VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_lettre ADD CONSTRAINT FK_6D9B225A2BDB92 FOREIGN KEY (lettre_id) REFERENCES lettre (id)');
        $this->addSql('CREATE INDEX IDX_6D9B225A2BDB92 ON inscription_lettre (lettre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_lettre DROP FOREIGN KEY FK_6D9B225A2BDB92');
        $this->addSql('DROP INDEX IDX_6D9B225A2BDB92 ON inscription_lettre');
        $this->addSql('ALTER TABLE inscription_lettre DROP lettre_id, DROP adresse_postale, DROP date_inscription, DROP moyen_paiement');
    }
}
