<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20261108150001 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Create the AI agent table (PostgreSQL).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_ai_platform_agent (id SERIAL NOT NULL, provider_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, plugin VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, prompt TEXT NOT NULL, default_prompt TEXT NOT NULL, model VARCHAR(255) DEFAULT NULL, required_capabilities JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX guiziweb_ai_agent_code ON guiziweb_sylius_ai_platform_agent (code)');
        $this->addSql('CREATE INDEX IDX_B895F3A53A8AA ON guiziweb_sylius_ai_platform_agent (provider_id)');
        $this->addSql('ALTER TABLE guiziweb_sylius_ai_platform_agent ADD CONSTRAINT FK_B895F3A53A8AA FOREIGN KEY (provider_id) REFERENCES guiziweb_sylius_ai_platform_provider (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guiziweb_sylius_ai_platform_agent');
    }
}
