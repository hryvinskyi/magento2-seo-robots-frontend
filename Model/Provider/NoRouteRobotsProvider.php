<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model\Provider;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\RobotsProviderInterface;
use Magento\Framework\App\HttpRequestInterface;

/**
 * Provides robots meta tags for 404 (noroute) pages
 */
class NoRouteRobotsProvider implements RobotsProviderInterface
{
    /**
     * Default priority for 404 pages
     */
    private const DEFAULT_PRIORITY = 5000;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly RobotsListInterface $robotsList,
        private readonly int $sortOrder = 20,
        private readonly int $priority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRobots(HttpRequestInterface $request): ?string
    {
        if ($request->getControllerName() !== 'noroute') {
            return null;
        }

        $routeRobotsTypes = $this->config->getNoRouteRobotsTypes();

        if (empty($routeRobotsTypes)) {
            return null;
        }

        return $this->robotsList->buildFromStructuredDirectives($routeRobotsTypes);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
