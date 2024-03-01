<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301020829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE invoice_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE invoice_user (id INT NOT NULL, invoice_id INT NOT NULL, customer_id INT DEFAULT NULL, creator_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F56B42C2989F1FD ON invoice_user (invoice_id)');
        $this->addSql('CREATE INDEX IDX_8F56B42C9395C3F3 ON invoice_user (customer_id)');
        $this->addSql('CREATE INDEX IDX_8F56B42C61220EA6 ON invoice_user (creator_id)');
        $this->addSql('ALTER TABLE invoice_user ADD CONSTRAINT FK_8F56B42C2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_user ADD CONSTRAINT FK_8F56B42C9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice_user ADD CONSTRAINT FK_8F56B42C61220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT fk_906517449395c3f3');
        $this->addSql('DROP INDEX idx_906517449395c3f3');
        $this->addSql('ALTER TABLE invoice ADD notes TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice DROP customer_id');
        $this->addSql('ALTER TABLE invoice DROP taxes');
        $this->addSql('ALTER TABLE invoice_product ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE invoice_user_id_seq CASCADE');
        $this->addSql('ALTER TABLE invoice_user DROP CONSTRAINT FK_8F56B42C2989F1FD');
        $this->addSql('ALTER TABLE invoice_user DROP CONSTRAINT FK_8F56B42C9395C3F3');
        $this->addSql('ALTER TABLE invoice_user DROP CONSTRAINT FK_8F56B42C61220EA6');
        $this->addSql('DROP TABLE invoice_user');
        $this->addSql('ALTER TABLE invoice ADD customer_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD taxes NUMERIC(5, 2) NOT NULL');
        $this->addSql('ALTER TABLE invoice DROP notes');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT fk_906517449395c3f3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_906517449395c3f3 ON invoice (customer_id)');
        $this->addSql('ALTER TABLE invoice_product ALTER created_at DROP DEFAULT');
    }
}
