<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108224434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE address_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE address (id INT NOT NULL, street_number VARCHAR(255) NOT NULL, street_type VARCHAR(255) NOT NULL, street_name VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) DEFAULT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, address_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, siret VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, website VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FBF094FF5B7AF75 ON company (address_id)');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, company_id INT NOT NULL, address_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_81398E09979B1AD6 ON customer (company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09F5B7AF75 ON customer (address_id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE quote ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT fk_1f7489c329fc6ae1');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT fk_1f7489c3bbb3772b');
        $this->addSql('DROP INDEX idx_1f7489c3bbb3772b');
        $this->addSql('DROP INDEX idx_1f7489c329fc6ae1');
        $this->addSql('ALTER TABLE quote_user ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quote_user ADD creator_id INT NOT NULL');
        $this->addSql('ALTER TABLE quote_user ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE quote_user ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE quote_user DROP creator_user_id');
        $this->addSql('ALTER TABLE quote_user DROP customer_user_id');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT FK_1F7489C39395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT FK_1F7489C361220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F7489C39395C3F3 ON quote_user (customer_id)');
        $this->addSql('CREATE INDEX IDX_1F7489C361220EA6 ON quote_user (creator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT FK_1F7489C39395C3F3');
        $this->addSql('DROP SEQUENCE address_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('ALTER TABLE company DROP CONSTRAINT FK_4FBF094FF5B7AF75');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09979B1AD6');
        $this->addSql('ALTER TABLE customer DROP CONSTRAINT FK_81398E09F5B7AF75');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE customer');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT FK_1F7489C361220EA6');
        $this->addSql('DROP INDEX IDX_1F7489C39395C3F3');
        $this->addSql('DROP INDEX IDX_1F7489C361220EA6');
        $this->addSql('ALTER TABLE quote_user ADD customer_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE quote_user DROP customer_id');
        $this->addSql('ALTER TABLE quote_user DROP created_at');
        $this->addSql('ALTER TABLE quote_user DROP updated_at');
        $this->addSql('ALTER TABLE quote_user RENAME COLUMN creator_id TO creator_user_id');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT fk_1f7489c329fc6ae1 FOREIGN KEY (creator_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT fk_1f7489c3bbb3772b FOREIGN KEY (customer_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1f7489c3bbb3772b ON quote_user (customer_user_id)');
        $this->addSql('CREATE INDEX idx_1f7489c329fc6ae1 ON quote_user (creator_user_id)');
        $this->addSql('ALTER TABLE quote ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE quote ALTER updated_at DROP DEFAULT');
    }
}
