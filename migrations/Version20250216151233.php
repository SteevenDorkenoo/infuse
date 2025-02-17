<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216151233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE favorites (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, recipe_id_id INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_E46960F59D86650F (user_id_id), INDEX IDX_E46960F569574A48 (recipe_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F59D86650F FOREIGN KEY (user_id_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F569574A48 FOREIGN KEY (recipe_id_id) REFERENCES recipes (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F59D86650F');
        $this->addSql('ALTER TABLE favorites DROP FOREIGN KEY FK_E46960F569574A48');
        $this->addSql('DROP TABLE favorites');
    }
}
