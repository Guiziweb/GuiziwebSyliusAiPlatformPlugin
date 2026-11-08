<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20261107220001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Create the AI provider table (PostgreSQL).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_ai_platform_provider (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, config JSON NOT NULL, enabled BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX guiziweb_ai_provider_code ON guiziweb_sylius_ai_platform_provider (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guiziweb_sylius_ai_platform_provider');
    }
}
