<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

trait GeneratesTheEncryptionKeyTrait
{
    private static function generateTheEncryptionKeyIfMissing(): void
    {
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'sylius:payment:generate-key']);
        $input->setInteractive(false);

        $application->run($input, new NullOutput());
    }
}
