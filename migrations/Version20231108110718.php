<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231108110718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE quote_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quote_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quote_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE quote (id INT NOT NULL, quote_status_id INT NOT NULL, total_amount NUMERIC(5, 2) NOT NULL, subtotal NUMERIC(5, 2) NOT NULL, taxes NUMERIC(5, 2) NOT NULL, notes TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expiry_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6B71CBF4EC1599C7 ON quote (quote_status_id)');
        $this->addSql('CREATE TABLE quote_status (id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE quote_user (id INT NOT NULL, quote_id INT NOT NULL, creator_user_id INT NOT NULL, customer_user_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1F7489C3DB805178 ON quote_user (quote_id)');
        $this->addSql('CREATE INDEX IDX_1F7489C329FC6AE1 ON quote_user (creator_user_id)');
        $this->addSql('CREATE INDEX IDX_1F7489C3BBB3772B ON quote_user (customer_user_id)');
        $this->addSql('ALTER TABLE quote ADD CONSTRAINT FK_6B71CBF4EC1599C7 FOREIGN KEY (quote_status_id) REFERENCES quote_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT FK_1F7489C3DB805178 FOREIGN KEY (quote_id) REFERENCES quote (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT FK_1F7489C329FC6AE1 FOREIGN KEY (creator_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quote_user ADD CONSTRAINT FK_1F7489C3BBB3772B FOREIGN KEY (customer_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE quote_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quote_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quote_user_id_seq CASCADE');
        $this->addSql('ALTER TABLE quote DROP CONSTRAINT FK_6B71CBF4EC1599C7');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT FK_1F7489C3DB805178');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT FK_1F7489C329FC6AE1');
        $this->addSql('ALTER TABLE quote_user DROP CONSTRAINT FK_1F7489C3BBB3772B');
        $this->addSql('DROP TABLE quote');
        $this->addSql('DROP TABLE quote_status');
        $this->addSql('DROP TABLE quote_user');
    }
}
