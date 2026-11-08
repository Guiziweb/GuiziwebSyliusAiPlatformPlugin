<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20261107220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the AI provider table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_ai_platform_provider (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, config JSON NOT NULL, enabled TINYINT NOT NULL, UNIQUE INDEX guiziweb_ai_provider_code (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guiziweb_sylius_ai_platform_provider');
    }
}
