<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191006201643 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE newsproviders (id INT NOT NULL, name VARCHAR(100) NOT NULL, url VARCHAR(155) NOT NULL, type VARCHAR(25) NOT NULL COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsprovidercategories (id INT NOT NULL, newsprovider_id INT DEFAULT NULL, `key` VARCHAR(255) NOT NULL COMMENT \'(DC2Type:App\\\\Types\\\\Category)\', path VARCHAR(255) NOT NULL, INDEX IDX_F3B479D685678FAB (newsprovider_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE newsprovidercategories ADD CONSTRAINT FK_F3B479D685678FAB FOREIGN KEY (newsprovider_id) REFERENCES newsproviders (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsprovidercategories DROP FOREIGN KEY FK_F3B479D685678FAB');
        $this->addSql('DROP TABLE newsproviders');
        $this->addSql('DROP TABLE newsprovidercategories');
    }
}
