<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin;

use Guiziweb\SyliusAiPlatformPlugin\DependencyInjection\Compiler\AttachAgentRowProcessorPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GuiziwebSyliusAiPlatformPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AttachAgentRowProcessorPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
