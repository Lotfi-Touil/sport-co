<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301011051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP CONSTRAINT fk_4fbf094ff5b7af75');
        $this->addSql('DROP INDEX uniq_4fbf094ff5b7af75');
        $this->addSql('ALTER TABLE company ADD address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE company DROP address_id');
        $this->addSql('ALTER TABLE "user" ADD address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE company ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE company DROP address');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT fk_4fbf094ff5b7af75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_4fbf094ff5b7af75 ON company (address_id)');
        $this->addSql('ALTER TABLE "user" DROP address');
    }
}
