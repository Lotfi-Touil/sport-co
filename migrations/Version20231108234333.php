<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108234333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE invoice_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_method_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quote_invoice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE invoice (id INT NOT NULL, invoice_status_id INT NOT NULL, total_amount NUMERIC(5, 2) NOT NULL, subtotal NUMERIC(5, 2) NOT NULL, taxes NUMERIC(5, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, submitted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expiry_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_90651744E58F121 ON invoice (invoice_status_id)');
        $this->addSql('CREATE TABLE invoice_status (id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE payment (id INT NOT NULL, invoice_id INT NOT NULL, payment_status_id INT NOT NULL, payment_method_id INT NOT NULL, amount NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6D28840D2989F1FD ON payment (invoice_id)');
        $this->addSql('CREATE INDEX IDX_6D28840D28DE2F95 ON payment (payment_status_id)');
        $this->addSql('CREATE INDEX IDX_6D28840D5AA1164F ON payment (payment_method_id)');
        $this->addSql('CREATE TABLE payment_method (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE payment_status (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quote_invoice (id INT NOT NULL, quote_id INT NOT NULL, invoice_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F8ABFC0DB805178 ON quote_invoice (quote_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F8ABFC02989F1FD ON quote_invoice (invoice_id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744E58F121 FOREIGN KEY (invoice_status_id) REFERENCES invoice_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D28DE2F95 FOREIGN KEY (payment_status_id) REFERENCES payment_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D5AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_invoice ADD CONSTRAINT FK_7F8ABFC0DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_invoice ADD CONSTRAINT FK_7F8ABFC02989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE company ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE quote ALTER updated_at DROP DEFAULT');
        $this->addSql('DROP INDEX idx_1f7489c3db805178');
        $this->addSql('ALTER TABLE quote_user ALTER updated_at DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1F7489C3DB805178 ON quote_user (quote_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE invoice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE invoice_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_method_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quote_invoice_id_seq CASCADE');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744E58F121');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D28DE2F95');
        $this->addSql('ALTER TABLE payment DROP CONSTRAINT FK_6D28840D5AA1164F');
        $this->addSql('ALTER TABLE quote_invoice DROP CONSTRAINT FK_7F8ABFC0DB805178');
        $this->addSql('ALTER TABLE quote_invoice DROP CONSTRAINT FK_7F8ABFC02989F1FD');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_status');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE payment_status');
        $this->addSql('DROP TABLE quote_invoice');
        $this->addSql('DROP INDEX UNIQ_1F7489C3DB805178');
        $this->addSql('ALTER TABLE quote_user ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX idx_1f7489c3db805178 ON quote_user (quote_id)');
        $this->addSql('ALTER TABLE quote ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE company ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
    }
}
