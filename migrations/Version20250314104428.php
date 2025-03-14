<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314104428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transactions ALTER category_id SET NOT NULL');
        $this->addSql('ALTER TABLE transactions ALTER amount TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE transactions ALTER amount DROP NOT NULL');
        $this->addSql('ALTER TABLE transactions ALTER date DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transactions ALTER category_id DROP NOT NULL');
        $this->addSql('ALTER TABLE transactions ALTER amount TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE transactions ALTER amount SET NOT NULL');
        $this->addSql('ALTER TABLE transactions ALTER date SET NOT NULL');
    }
}
