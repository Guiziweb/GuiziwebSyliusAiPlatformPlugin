<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Form\Type;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, ['label' => 'guiziweb_sylius_ai_platform.form.provider.code'])
            ->add('enabled', CheckboxType::class, ['label' => 'guiziweb_sylius_ai_platform.form.provider.enabled', 'required' => false])
            ->addEventListener(FormEvents::PRE_SET_DATA, $this->addConfigurationForTheProviderType(...))
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_ai_platform_provider';
    }

    private function addConfigurationForTheProviderType(FormEvent $event): void
    {
        $provider = $event->getData();

        if (!$provider instanceof ProviderInterface || null === $provider->getType()) {
            return;
        }

        $form = $event->getForm();

        if (null !== $provider->getId()) {
            $form->add('code', TextType::class, ['label' => 'guiziweb_sylius_ai_platform.form.provider.code', 'disabled' => true]);
        }

        $form
            ->add('config', ProviderConfigurationType::class, ['label' => false, 'provider_type' => $provider->getType()])
            ->add('type', TextType::class, ['label' => 'guiziweb_sylius_ai_platform.form.provider.type', 'disabled' => true])
        ;
    }
}
