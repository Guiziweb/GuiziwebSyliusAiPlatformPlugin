<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Form\Type;

use Doctrine\ORM\EntityRepository;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Agent\AgentInterface;
use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ChatModelNamesInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

final class AgentType extends AbstractType
{
    /**
     * @param class-string $providerClass
     * @param RepositoryInterface<ProviderInterface> $providerRepository
     */
    public function __construct(
        private readonly string $providerClass,
        private readonly RepositoryInterface $providerRepository,
        private readonly ChatModelNamesInterface $chatModelNames,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prompt', TextareaType::class, [
                'label' => 'guiziweb_sylius_ai_platform.form.agent.prompt',
                'attr' => ['rows' => 12],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, $this->offerTheSavedChoices(...))
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->offerTheModelsOfTheSubmittedProvider(...))
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_ai_platform_agent';
    }

    private function offerTheSavedChoices(FormEvent $event): void
    {
        $agent = $event->getData();

        if (!$agent instanceof AgentInterface) {
            return;
        }

        $this->addProviderChoice($event->getForm(), $agent->getProvider());
        $this->addModelChoice($event->getForm(), $agent->getProvider(), $agent->getModel());
    }

    private function offerTheModelsOfTheSubmittedProvider(FormEvent $event): void
    {
        $agent = $event->getForm()->getData();
        $submitted = $event->getData();

        if (!$agent instanceof AgentInterface || !\is_array($submitted)) {
            return;
        }

        $providerId = $submitted['provider'] ?? null;
        $provider = \is_scalar($providerId) && ctype_digit((string) $providerId) ? $this->providerRepository->find((int) $providerId) : null;
        $provider = $provider instanceof ProviderInterface ? $provider : null;

        $keptModel = null !== $provider && $provider->getId() === $agent->getProvider()?->getId() ? $agent->getModel() : null;

        $this->addModelChoice($event->getForm(), $provider, $keptModel);
    }

    private function addProviderChoice(FormInterface $form, ?ProviderInterface $current): void
    {
        $currentId = $current?->getId() ?? 0;

        $form->add('provider', EntityType::class, [
            'class' => $this->providerClass,
            'choice_label' => 'code',
            'required' => false,
            'placeholder' => 'guiziweb_sylius_ai_platform.form.agent.no_provider',
            'label' => 'guiziweb_sylius_ai_platform.form.agent.provider',
            'query_builder' => static fn (EntityRepository $repository) => $repository->createQueryBuilder('provider')
                ->andWhere('provider.enabled = true OR provider.id = :current')
                ->setParameter('current', $currentId)
                ->orderBy('provider.code'),
        ]);
    }

    private function addModelChoice(FormInterface $form, ?ProviderInterface $provider, ?string $keptModel): void
    {
        $names = null === $provider ? [] : $this->chatModelNames->forProvider($provider);
        $listed = [] !== $names;

        if (null !== $provider && null !== $keptModel && !\in_array($keptModel, $names, true)) {
            $names[] = $keptModel;
        }

        $form->add('model', ChoiceType::class, [
            'choices' => array_combine($names, $names),
            'required' => false,
            'placeholder' => 'guiziweb_sylius_ai_platform.form.agent.no_model',
            'label' => 'guiziweb_sylius_ai_platform.form.agent.model',
            'help' => match (true) {
                null === $provider => 'guiziweb_sylius_ai_platform.form.agent.model_needs_provider',
                !$listed => 'guiziweb_sylius_ai_platform.form.agent.no_models_listed',
                default => null,
            },
        ]);
    }
}
