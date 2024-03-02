<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240302125644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_access ADD can_access BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access DROP can_show');
        $this->addSql('ALTER TABLE page_access DROP can_edit');
        $this->addSql('ALTER TABLE page_access DROP can_new');
        $this->addSql('ALTER TABLE page_access DROP can_delete');
        $this->addSql('ALTER TABLE page_access DROP can_send');
        $this->addSql('ALTER TABLE page_access DROP can_download');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE page_access ADD can_edit BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access ADD can_new BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access ADD can_delete BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access ADD can_send BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access ADD can_download BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE page_access RENAME COLUMN can_access TO can_show');
    }
}
