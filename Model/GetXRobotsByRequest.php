<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\RequestInterface;

/**
 * Get X-Robots-Tag directives based on priority from all registered providers
 */
class GetXRobotsByRequest implements GetXRobotsByRequestInterface
{
    /**
     * @var array<XRobotsProviderInterface>
     */
    private array $sortedProviders;

    /**
     * @param RequestInterface $request
     * @param array<XRobotsProviderInterface> $xrobotsProviders
     */
    public function __construct(
        private readonly RequestInterface $request,
        array $xrobotsProviders = []
    ) {
        $this->sortedProviders = $this->sortProviders($xrobotsProviders);
    }

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        $highestPriority = null;
        $winningDirectives = null;

        foreach ($this->sortedProviders as $provider) {
            $directives = $provider->getDirectives($this->request);
            if ($directives !== null) {
                $priority = $provider->getPriority();
                if ($highestPriority === null || $priority > $highestPriority) {
                    $highestPriority = $priority;
                    $winningDirectives = $directives;
                }
            }
        }

        return $winningDirectives ?? [];
    }

    /**
     * Sort providers by sort order (lower executes first)
     *
     * @param array<XRobotsProviderInterface> $providers
     * @return array<XRobotsProviderInterface>
     */
    private function sortProviders(array $providers): array
    {
        usort($providers, static function (XRobotsProviderInterface $a, XRobotsProviderInterface $b): int {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $providers;
    }
}
