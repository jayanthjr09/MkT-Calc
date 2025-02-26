<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250225094251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_set (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temperature_reading (id INT AUTO_INCREMENT NOT NULL, data_set_id INT NOT NULL, timestamp DATETIME NOT NULL, temperature DOUBLE PRECISION NOT NULL, INDEX IDX_C37E137370053C01 (data_set_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE temperature_reading ADD CONSTRAINT FK_C37E137370053C01 FOREIGN KEY (data_set_id) REFERENCES data_set (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temperature_reading DROP FOREIGN KEY FK_C37E137370053C01');
        $this->addSql('DROP TABLE data_set');
        $this->addSql('DROP TABLE temperature_reading');
    }
}
