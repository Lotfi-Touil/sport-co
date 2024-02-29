<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301205020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE report (id INT NOT NULL, company_id INT DEFAULT NULL, total_revenue DOUBLE PRECISION NOT NULL, total_expenses DOUBLE PRECISION NOT NULL, net_profit DOUBLE PRECISION NOT NULL, payment_details TEXT NOT NULL, top_selling_products TEXT NOT NULL, new_customers_count INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F7784979B1AD6 ON report (company_id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE report_id_seq CASCADE');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784979B1AD6');
        $this->addSql('DROP TABLE report');
    }
}
