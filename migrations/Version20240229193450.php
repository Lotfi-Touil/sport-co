<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229193450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE basic_email_template_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE basic_email_template (id INT NOT NULL, type_id INT NOT NULL, subjet VARCHAR(255) NOT NULL, body TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E0E9FA56C54C8C93 ON basic_email_template (type_id)');
        $this->addSql('ALTER TABLE basic_email_template ADD CONSTRAINT FK_E0E9FA56C54C8C93 FOREIGN KEY (type_id) REFERENCES email_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE basic_email_template_id_seq CASCADE');
        $this->addSql('ALTER TABLE basic_email_template DROP CONSTRAINT FK_E0E9FA56C54C8C93');
        $this->addSql('DROP TABLE basic_email_template');
    }
}
