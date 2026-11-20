<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Agent;

use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderRoutingListener;
use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\InputProcessor\SystemPromptInputProcessor;
use Symfony\AI\Agent\InputProcessorInterface;

final class AgentRowProcessor implements InputProcessorInterface
{
    public function __construct(
        private readonly string $agentCode,
        private readonly AgentRowReaderInterface $agentRowReader,
    ) {
    }

    public function processInput(Input $input): void
    {
        $agent = $this->agentRowReader->read($this->agentCode);

        if (null === $agent) {
            return;
        }

        $prompt = $agent->getPrompt();

        if (null !== $prompt && '' !== $prompt) {
            (new SystemPromptInputProcessor($prompt))->processInput($input);
        }

        if (!$agent->usesOwnProvider()) {
            return;
        }

        $input->setOptions([...$input->getOptions(), ProviderRoutingListener::PROVIDER_OPTION => $agent->getProvider()?->getId()]);

        if (!\array_key_exists('model', $input->getOptions())) {
            $input->setModel((string) $agent->getModel());
        }
    }
}
