<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218193937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
         // Ajouter d'abord la colonne comme nullable
        $this->addSql('ALTER TABLE invoice ADD customer_id INT DEFAULT NULL');

        // Mettre à jour les factures existantes avec l'id de client 1
        // Assurez-vous que le client avec l'id 1 existe dans la table customer
        $this->addSql('UPDATE invoice SET customer_id = 1 WHERE customer_id IS NULL');

        // Ensuite, changer la colonne en non nullable
        $this->addSql('ALTER TABLE invoice ALTER COLUMN customer_id SET NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_906517449395C3F3 ON invoice (customer_id)');
        $this->addSql('ALTER TABLE payment ALTER is_recurring DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE payment ALTER is_recurring SET DEFAULT false');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449395C3F3');
        $this->addSql('DROP INDEX IDX_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice DROP customer_id');
    }
}
