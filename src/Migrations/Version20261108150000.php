<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20261108150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the AI agent table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE guiziweb_sylius_ai_platform_agent (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, prompt LONGTEXT NOT NULL, default_prompt LONGTEXT NOT NULL, model VARCHAR(255) DEFAULT NULL, provider_id INT DEFAULT NULL, INDEX IDX_B895F3A53A8AA (provider_id), UNIQUE INDEX guiziweb_ai_agent_code (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('ALTER TABLE guiziweb_sylius_ai_platform_agent ADD CONSTRAINT FK_B895F3A53A8AA FOREIGN KEY (provider_id) REFERENCES guiziweb_sylius_ai_platform_provider (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guiziweb_sylius_ai_platform_agent DROP FOREIGN KEY FK_B895F3A53A8AA');
        $this->addSql('DROP TABLE guiziweb_sylius_ai_platform_agent');
    }
}
