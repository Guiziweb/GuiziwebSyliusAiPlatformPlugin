<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusAiPlatformPlugin\SampleAssistant;

use Symfony\AI\Agent\AgentInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'guiziweb:sample:ask', description: 'Ask the sample assistant, which answers with what it received')]
final class AskSampleAssistantCommand extends Command
{
    public function __construct(#[Autowire(service: 'ai.agent.sample_assistant')] private readonly AgentInterface $agent)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('question', InputArgument::OPTIONAL, 'The question', 'Hello');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $question = $input->getArgument('question');
        $output->writeln((string) $this->agent->call(is_string($question) ? $question : 'Hello')->getContent());

        return Command::SUCCESS;
    }
}
