<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130220635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE quote_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE quote_product (id INT NOT NULL, quote_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, price NUMERIC(8, 2) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3CA5AC29DB805178 ON quote_product (quote_id)');
        $this->addSql('CREATE INDEX IDX_3CA5AC294584665A ON quote_product (product_id)');
        $this->addSql('ALTER TABLE quote_product ADD CONSTRAINT FK_3CA5AC29DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_product ADD CONSTRAINT FK_3CA5AC294584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE quote_product_id_seq CASCADE');
        $this->addSql('ALTER TABLE quote_product DROP CONSTRAINT FK_3CA5AC29DB805178');
        $this->addSql('ALTER TABLE quote_product DROP CONSTRAINT FK_3CA5AC294584665A');
        $this->addSql('DROP TABLE quote_product');
    }
}
