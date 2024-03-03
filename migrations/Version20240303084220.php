<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303084220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_status ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice_status ADD CONSTRAINT FK_C036F84F979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C036F84F979B1AD6 ON invoice_status (company_id)');
        $this->addSql('ALTER TABLE quote_status ADD company_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quote_status ADD CONSTRAINT FK_41B8CB5F979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_41B8CB5F979B1AD6 ON quote_status (company_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invoice_status DROP CONSTRAINT FK_C036F84F979B1AD6');
        $this->addSql('DROP INDEX IDX_C036F84F979B1AD6');
        $this->addSql('ALTER TABLE invoice_status DROP company_id');
        $this->addSql('ALTER TABLE quote_status DROP CONSTRAINT FK_41B8CB5F979B1AD6');
        $this->addSql('DROP INDEX IDX_41B8CB5F979B1AD6');
        $this->addSql('ALTER TABLE quote_status DROP company_id');
    }
}
