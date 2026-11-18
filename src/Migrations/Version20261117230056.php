<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261117230056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create application tables for MySQL and MariaDB.';
    }

    public function up(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('CREATE TABLE gallery_session (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE gallery_image (
            id INT AUTO_INCREMENT NOT NULL,
            filename VARCHAR(255) DEFAULT NULL,
            gallery_session_id INT NOT NULL,
            INDEX IDX_21A0D47CED31DD9 (gallery_session_id),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            confirm_account VARCHAR(100) DEFAULT NULL,
            created_date VARCHAR(255) DEFAULT NULL,
            ip VARCHAR(255) DEFAULT NULL,
            login VARCHAR(255) DEFAULT NULL,
            UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE visit (
            id INT AUTO_INCREMENT NOT NULL,
            visitor_id VARCHAR(64) DEFAULT NULL,
            visited_at DATETIME DEFAULT NULL,
            ip_adress VARCHAR(100) DEFAULT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('CREATE TABLE messenger_messages (
            id BIGINT AUTO_INCREMENT NOT NULL,
            body LONGTEXT NOT NULL,
            headers LONGTEXT NOT NULL,
            queue_name VARCHAR(190) NOT NULL,
            created_at DATETIME NOT NULL,
            available_at DATETIME NOT NULL,
            delivered_at DATETIME DEFAULT NULL,
            INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750
                (queue_name, available_at, delivered_at, id),
            PRIMARY KEY (id)
        )');

        $this->addSql('ALTER TABLE gallery_image
            ADD CONSTRAINT FK_21A0D47CED31DD9
            FOREIGN KEY (gallery_session_id)
            REFERENCES gallery_session (id)');
    }

    public function down(Schema $schema): void
    {
        if (!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform) {
            return;
        }

        $this->addSql('ALTER TABLE gallery_image DROP FOREIGN KEY FK_21A0D47CED31DD9');
        $this->addSql('DROP TABLE gallery_image');
        $this->addSql('DROP TABLE gallery_session');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE visit');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
