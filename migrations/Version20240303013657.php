<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303013657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE product_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE product_product_category (product_id INT NOT NULL, product_category_id INT NOT NULL, PRIMARY KEY(product_id, product_category_id))');
        $this->addSql('CREATE INDEX IDX_437017AA4584665A ON product_product_category (product_id)');
        $this->addSql('CREATE INDEX IDX_437017AABE6903FD ON product_product_category (product_category_id)');
        $this->addSql('CREATE TABLE product_category (id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(600) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE product_product_category ADD CONSTRAINT FK_437017AA4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_product_category ADD CONSTRAINT FK_437017AABE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE product_category_id_seq CASCADE');
        $this->addSql('ALTER TABLE product_product_category DROP CONSTRAINT FK_437017AA4584665A');
        $this->addSql('ALTER TABLE product_product_category DROP CONSTRAINT FK_437017AABE6903FD');
        $this->addSql('DROP TABLE product_product_category');
        $this->addSql('DROP TABLE product_category');
    }
}
