<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191009205511 extends AbstractMigration
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

        $this->addSql("INSERT INTO `newsproviders` (`id`, `name`, `url`, `type`) VALUES
                        (2, 'Kibris Postasi', 'http://www.kibrispostasi.com', 'kibrispostasi'),
                        (3, 'Gundem Kibris', 'http://www.gundemkibris.com', 'tebilisim'),
                        (4, 'Detay Kibris', 'http://www.detaykibris.com', 'cmhaber');");

        $this->addSql("INSERT INTO `newsprovidercategories` (`id`, `newsprovider_id`, `category_key`, `path`) VALUES
                        (1, 2, 'kibris', 'c35-KIBRIS_HABERLERI'),
                        (2, 3, 'dunya', 'dunya'),
                        (3, 3, 'kibris', 'kibris'),
                        (4, 4, 'dunya', 'dunya-haberleri-45hk.htm'),
                        (5, 4, 'kibris', 'kibris-haberleri-7hk.htm');");
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
