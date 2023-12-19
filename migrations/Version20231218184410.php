<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218184410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment ADD is_recurring BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE payment ADD stripe_subscription_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD stripe_product_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD stripe_price_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE payment DROP is_recurring');
        $this->addSql('ALTER TABLE payment DROP stripe_subscription_id');
        $this->addSql('ALTER TABLE product DROP stripe_product_id');
        $this->addSql('ALTER TABLE product DROP stripe_price_id');
    }
}
