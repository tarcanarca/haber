<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191011195416 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsproviders CHANGE name name VARCHAR(100) NOT NULL, CHANGE url url VARCHAR(155) NOT NULL');
        $this->addSql('ALTER TABLE rawposts CHANGE provider_key provider_key VARCHAR(155) NOT NULL, CHANGE url url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE newsprovidercategories CHANGE path path VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE newsprovidercategories CHANGE path path VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\'');
        $this->addSql('ALTER TABLE newsproviders CHANGE name name VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\', CHANGE url url VARCHAR(155) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\'');
        $this->addSql('ALTER TABLE rawposts CHANGE provider_key provider_key VARCHAR(155) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\', CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:App\\\\Types\\\\ProviderType)\'');
    }
}
