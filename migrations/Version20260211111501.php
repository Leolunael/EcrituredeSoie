<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211111501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE perm_presentation');
        $this->addSql('ALTER TABLE inscription_atelier DROP user_type, DROP external_user_id');
        $this->addSql('ALTER TABLE inscription_lettre DROP user_type, DROP external_user_id');
        $this->addSql('ALTER TABLE inscription_visio DROP user_type, DROP external_user_id');
        $this->addSql('ALTER TABLE inscription_vollon DROP user_type, DROP external_user_id');
        $this->addSql('ALTER TABLE user DROP is_permanent, DROP mongodb_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE perm_presentation (id INT AUTO_INCREMENT NOT NULL, titre LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, avantages LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE inscription_atelier ADD user_type VARCHAR(50) DEFAULT NULL, ADD external_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_lettre ADD user_type VARCHAR(50) DEFAULT NULL, ADD external_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_visio ADD user_type VARCHAR(50) DEFAULT NULL, ADD external_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription_vollon ADD user_type VARCHAR(50) DEFAULT NULL, ADD external_user_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_permanent TINYINT(1) DEFAULT 0 NOT NULL, ADD mongodb_id VARCHAR(255) DEFAULT NULL');
    }
}
