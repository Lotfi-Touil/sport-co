<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231217020755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE company ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER submitted_at DROP NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER expiry_date DROP NOT NULL');
        $this->addSql('ALTER TABLE quote_product ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE quote_product ALTER updated_at DROP NOT NULL');
        $this->addSql('ALTER TABLE quote_user ALTER created_at DROP NOT NULL');
        $this->addSql('ALTER TABLE quote_user ALTER updated_at DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER submitted_at SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ALTER expiry_date SET NOT NULL');
        $this->addSql('ALTER TABLE quote_user ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE quote_user ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE quote_product ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE quote_product ALTER updated_at SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER created_at SET NOT NULL');
        $this->addSql('ALTER TABLE company ALTER updated_at SET NOT NULL');
    }
}
