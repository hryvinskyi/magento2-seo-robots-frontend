<?php
/**
 * Copyright (c) 2021-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\View\Page\Config;

class ApplyRobots implements ApplyRobotsInterface
{
    public function __construct(
        private readonly IsIgnoredActionsInterface $ignoredActions,
        private readonly IsIgnoredUrlsInterface $isIgnoredUrls,
        private readonly GetRobotsByRequestInterface $getRobotsByRequest,
        private readonly RobotsListInterface $robotsList,
        private readonly ConfigInterface $config,
        private array $robotsProviders = []
    ) {
        $this->robotsProviders = $this->sortProviders($robotsProviders);
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request, Config $pageConfig): void
    {
        if ($this->config->isEnabled() === false || $this->isIgnoredUrls->execute() === true
            || $this->ignoredActions->execute($request) === true
        ) {
            return;
        }

        if ($robots = $this->getRobotsByRequest->execute($request)) {
            $pageConfig->setRobots($robots);
        }

        $routeRobotsTypes = $this->config->getNoRouteRobotsTypes();

        if (!empty($routeRobotsTypes) && $request->getControllerName() === 'noroute') {
            $pageConfig->setRobots($this->robotsList->buildFromStructuredDirectives($routeRobotsTypes));
        }

        // Check URL query params directly
        $queryParams = $request->getQuery()->toArray();
        if (isset($queryParams['p'])) {
            // Pagination only (no filters) - 'p' is the only query parameter
            if (count($queryParams) === 1 && $this->config->isPaginatedRobots() === true) {
                $directives = $this->config->getPaginatedMetaRobots();
                $pageConfig->setRobots($this->robotsList->buildFromStructuredDirectives($directives));
            }
            // Pagination with filters - 'p' plus other query parameters
            elseif (count($queryParams) > 1 && $this->config->isPaginatedFilteredRobots() === true) {
                $directives = $this->config->getPaginatedFilteredMetaRobots();
                $pageConfig->setRobots($this->robotsList->buildFromStructuredDirectives($directives));
            }
        }

        // Apply custom robots from providers
        foreach ($this->robotsProviders as $provider) {
            if ($providerRobots = $provider->getRobots($request)) {
                $pageConfig->setRobots($providerRobots);
            }
        }
    }

    /**
     * Sort providers by sort order
     *
     * @param array $providers
     * @return array
     */
    private function sortProviders(array $providers): array
    {
        usort($providers, function (RobotsProviderInterface $a, RobotsProviderInterface $b) {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $providers;
    }
}
