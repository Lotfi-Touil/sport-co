<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229153508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE email_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE email_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE email_template (id INT NOT NULL, company_id INT NOT NULL, type_id INT NOT NULL, subject VARCHAR(255) NOT NULL, body TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C0600CA979B1AD6 ON email_template (company_id)');
        $this->addSql('CREATE INDEX IDX_9C0600CAC54C8C93 ON email_template (type_id)');
        $this->addSql('CREATE TABLE email_type (id INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CA979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CAC54C8C93 FOREIGN KEY (type_id) REFERENCES email_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE email_template_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE email_type_id_seq CASCADE');
        $this->addSql('ALTER TABLE email_template DROP CONSTRAINT FK_9C0600CA979B1AD6');
        $this->addSql('ALTER TABLE email_template DROP CONSTRAINT FK_9C0600CAC54C8C93');
        $this->addSql('DROP TABLE email_template');
        $this->addSql('DROP TABLE email_type');
    }
}
