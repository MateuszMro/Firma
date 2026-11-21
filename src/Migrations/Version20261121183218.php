<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261121183218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE gallery_session_gallery_category (gallery_session_id INT NOT NULL, gallery_category_id INT NOT NULL, INDEX IDX_A2E88BB0ED31DD9 (gallery_session_id), INDEX IDX_A2E88BB0FB4AD426 (gallery_category_id), PRIMARY KEY (gallery_session_id, gallery_category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE gallery_session_gallery_category ADD CONSTRAINT FK_A2E88BB0ED31DD9 FOREIGN KEY (gallery_session_id) REFERENCES gallery_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE gallery_session_gallery_category ADD CONSTRAINT FK_A2E88BB0FB4AD426 FOREIGN KEY (gallery_category_id) REFERENCES gallery_category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_session_gallery_category DROP FOREIGN KEY FK_A2E88BB0ED31DD9');
        $this->addSql('ALTER TABLE gallery_session_gallery_category DROP FOREIGN KEY FK_A2E88BB0FB4AD426');
        $this->addSql('DROP TABLE gallery_category');
        $this->addSql('DROP TABLE gallery_session_gallery_category');
    }
}
