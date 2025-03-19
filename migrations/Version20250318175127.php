<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250318175127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX id_fav_idx ON favorites (id)');
        $this->addSql('CREATE INDEX id_idx ON recipes (id)');
        $this->addSql('CREATE INDEX title_idx ON recipes (title)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX id_fav_idx ON favorites');
        $this->addSql('DROP INDEX id_idx ON recipes');
        $this->addSql('DROP INDEX title_idx ON recipes');
    }
}
