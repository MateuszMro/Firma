<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102233442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visit ADD COLUMN ip_adress VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__visit AS SELECT id, visitor_id, visited_at FROM visit');
        $this->addSql('DROP TABLE visit');
        $this->addSql('CREATE TABLE visit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, visitor_id VARCHAR(64) DEFAULT NULL, visited_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO visit (id, visitor_id, visited_at) SELECT id, visitor_id, visited_at FROM __temp__visit');
        $this->addSql('DROP TABLE __temp__visit');
    }
}
