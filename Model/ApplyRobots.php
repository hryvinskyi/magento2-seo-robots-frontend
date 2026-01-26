<?php
/**
 * Copyright (c) 2021-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\View\Page\Config;

/**
 * Applies robots meta tags based on priority from all registered providers
 */
class ApplyRobots implements ApplyRobotsInterface
{
    /**
     * @var array<RobotsProviderInterface>
     */
    private array $sortedProviders;

    /**
     * @param IsIgnoredActionsInterface $ignoredActions
     * @param IsIgnoredUrlsInterface $isIgnoredUrls
     * @param ConfigInterface $config
     * @param array<RobotsProviderInterface> $robotsProviders
     */
    public function __construct(
        private readonly IsIgnoredActionsInterface $ignoredActions,
        private readonly IsIgnoredUrlsInterface $isIgnoredUrls,
        private readonly ConfigInterface $config,
        array $robotsProviders = []
    ) {
        $this->sortedProviders = $this->sortProviders($robotsProviders);
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request, Config $pageConfig): void
    {
        if (!$this->config->isEnabled() || $this->isIgnoredUrls->execute()
            || $this->ignoredActions->execute($request)
        ) {
            return;
        }

        $highestPriority = null;
        $winningRobots = null;

        foreach ($this->sortedProviders as $provider) {
            $robots = $provider->getRobots($request);
            if ($robots !== null) {
                $priority = $provider->getPriority();
                if ($highestPriority === null || $priority > $highestPriority) {
                    $highestPriority = $priority;
                    $winningRobots = $robots;
                }
            }
        }

        if ($winningRobots !== null) {
            $pageConfig->setRobots($winningRobots);
        }
    }

    /**
     * Sort providers by sort order (lower executes first)
     *
     * @param array<RobotsProviderInterface> $providers
     * @return array<RobotsProviderInterface>
     */
    private function sortProviders(array $providers): array
    {
        usort($providers, static function (RobotsProviderInterface $a, RobotsProviderInterface $b): int {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $providers;
    }
}
