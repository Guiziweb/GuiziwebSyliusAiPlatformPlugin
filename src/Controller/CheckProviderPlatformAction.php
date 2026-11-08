<?php

declare(strict_types=1);

namespace Guiziweb\SyliusAiPlatformPlugin\Controller;

use Guiziweb\SyliusAiPlatformPlugin\Entity\Provider\ProviderInterface;
use Guiziweb\SyliusAiPlatformPlugin\Platform\ProviderPlatformFactory;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CheckProviderPlatformAction extends AbstractController
{
    /**
     * @param RepositoryInterface<ProviderInterface> $providerRepository
     */
    public function __construct(
        private readonly RepositoryInterface $providerRepository,
        private readonly ProviderPlatformFactory $platformFactory,
    ) {
    }

    public function __invoke(int $id): RedirectResponse
    {
        $provider = $this->providerRepository->find($id) ?? throw new NotFoundHttpException();

        try {
            $this->platformFactory->create($provider);
            $this->addFlash('success', 'guiziweb_sylius_ai_platform.check.platform_built');
        } catch (\Throwable $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('guiziweb_sylius_ai_platform_admin_provider_index');
    }
}
