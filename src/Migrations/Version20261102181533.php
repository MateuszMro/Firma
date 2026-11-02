<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261102181533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery_image (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) DEFAULT NULL, gallery_session_id INTEGER NOT NULL, CONSTRAINT FK_21A0D47CED31DD9 FOREIGN KEY (gallery_session_id) REFERENCES gallery_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_21A0D47CED31DD9 ON gallery_image (gallery_session_id)');
        $this->addSql('CREATE TABLE gallery_session (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL, created_at VARCHAR(255) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE gallery_image');
        $this->addSql('DROP TABLE gallery_session');
    }
}
