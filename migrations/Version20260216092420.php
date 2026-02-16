<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216092420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_lettre DROP FOREIGN KEY FK_6D9B225A2BDB92');
        $this->addSql('ALTER TABLE inscription_lettre DROP FOREIGN KEY FK_6D9B22A76ED395');
        $this->addSql('DROP INDEX IDX_6D9B225A2BDB92 ON inscription_lettre');
        $this->addSql('ALTER TABLE inscription_lettre ADD admin_id INT DEFAULT NULL, DROP lettre_id, DROP adresse_postale, DROP date_inscription, DROP moyen_paiement, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE inscription_lettre ADD CONSTRAINT FK_6D9B22642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE inscription_lettre ADD CONSTRAINT FK_6D9B22A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6D9B22642B8210 ON inscription_lettre (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription_lettre DROP FOREIGN KEY FK_6D9B22642B8210');
        $this->addSql('ALTER TABLE inscription_lettre DROP FOREIGN KEY FK_6D9B22A76ED395');
        $this->addSql('DROP INDEX IDX_6D9B22642B8210 ON inscription_lettre');
        $this->addSql('ALTER TABLE inscription_lettre ADD lettre_id INT NOT NULL, ADD adresse_postale VARCHAR(255) NOT NULL, ADD date_inscription DATETIME NOT NULL, ADD moyen_paiement VARCHAR(50) NOT NULL, DROP admin_id, CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE prenom prenom VARCHAR(100) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('ALTER TABLE inscription_lettre ADD CONSTRAINT FK_6D9B225A2BDB92 FOREIGN KEY (lettre_id) REFERENCES lettre (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_lettre ADD CONSTRAINT FK_6D9B22A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6D9B225A2BDB92 ON inscription_lettre (lettre_id)');
    }
}
