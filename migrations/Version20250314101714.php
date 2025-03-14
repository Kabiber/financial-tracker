<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314101714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_category_name_user');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT FK_EAA81A4C12469DE2');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_name_user ON categories (name, user_id)');
        $this->addSql('ALTER TABLE transactions DROP CONSTRAINT fk_eaa81a4c12469de2');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT fk_eaa81a4c12469de2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
