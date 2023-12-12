<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231217020309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ALTER total_amount TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE invoice ALTER subtotal TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE payment ALTER amount TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE product ALTER price TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE quote ALTER total_amount TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE quote ALTER subtotal TYPE NUMERIC(13, 4)');
        $this->addSql('ALTER TABLE quote_product ALTER price TYPE NUMERIC(13, 4)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quote_product ALTER price TYPE NUMERIC(8, 2)');
        $this->addSql('ALTER TABLE quote ALTER total_amount TYPE NUMERIC(5, 2)');
        $this->addSql('ALTER TABLE quote ALTER subtotal TYPE NUMERIC(5, 2)');
        $this->addSql('ALTER TABLE payment ALTER amount TYPE NUMERIC(5, 2)');
        $this->addSql('ALTER TABLE product ALTER price TYPE NUMERIC(8, 2)');
        $this->addSql('ALTER TABLE invoice ALTER total_amount TYPE NUMERIC(5, 2)');
        $this->addSql('ALTER TABLE invoice ALTER subtotal TYPE NUMERIC(5, 2)');
    }
}
