<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210210528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_recipes (category_id INT NOT NULL, recipes_id INT NOT NULL, INDEX IDX_64B1A2CB12469DE2 (category_id), INDEX IDX_64B1A2CBFDF2B1FA (recipes_id), PRIMARY KEY(category_id, recipes_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_recipes ADD CONSTRAINT FK_64B1A2CB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_recipes ADD CONSTRAINT FK_64B1A2CBFDF2B1FA FOREIGN KEY (recipes_id) REFERENCES recipes (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_recipes DROP FOREIGN KEY FK_64B1A2CB12469DE2');
        $this->addSql('ALTER TABLE category_recipes DROP FOREIGN KEY FK_64B1A2CBFDF2B1FA');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_recipes');
    }
}
