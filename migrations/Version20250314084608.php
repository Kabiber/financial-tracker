<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314084608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactions ALTER COLUMN category_id DROP NOT NULL');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT fk_eaa81a4c12469de2');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT fk_eaa81a4c12469de2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT fk_eaa81a4c12469de2');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT fk_eaa81a4c12469de2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE transactions ALTER COLUMN category_id SET NOT NULL');
    }
}
