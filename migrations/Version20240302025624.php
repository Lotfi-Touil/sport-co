<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240302025624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE page_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE page_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE page (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE page_access (id INT NOT NULL, employe_id INT DEFAULT NULL, page_id INT DEFAULT NULL, can_show BOOLEAN DEFAULT NULL, can_edit BOOLEAN DEFAULT NULL, can_new BOOLEAN DEFAULT NULL, can_delete BOOLEAN DEFAULT NULL, can_send BOOLEAN DEFAULT NULL, can_download BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69BFDD4A1B65292 ON page_access (employe_id)');
        $this->addSql('CREATE INDEX IDX_69BFDD4AC4663E4 ON page_access (page_id)');
        $this->addSql('ALTER TABLE page_access ADD CONSTRAINT FK_69BFDD4A1B65292 FOREIGN KEY (employe_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE page_access ADD CONSTRAINT FK_69BFDD4AC4663E4 FOREIGN KEY (page_id) REFERENCES page (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE page_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE page_access_id_seq CASCADE');
        $this->addSql('ALTER TABLE page_access DROP CONSTRAINT FK_69BFDD4A1B65292');
        $this->addSql('ALTER TABLE page_access DROP CONSTRAINT FK_69BFDD4AC4663E4');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE page_access');
    }
}
