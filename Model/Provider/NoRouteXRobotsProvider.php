<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model\Provider;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\XRobotsProviderInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Provides X-Robots-Tag directives for 404 (noroute) pages
 */
class NoRouteXRobotsProvider implements XRobotsProviderInterface
{
    /**
     * Default priority for 404 pages
     */
    private const DEFAULT_PRIORITY = 5000;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly int $sortOrder = 20,
        private readonly int $priority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDirectives(RequestInterface $request): ?array
    {
        if ($request->getControllerName() !== 'noroute') {
            return null;
        }

        // Try X-Robots specific directives first
        $noRouteDirectives = $this->config->getNoRouteXRobotsTypes();
        if (!empty($noRouteDirectives)) {
            return $noRouteDirectives;
        }

        // Fall back to meta robots 404 directives
        $metaNoRouteDirectives = $this->config->getNoRouteRobotsTypes();
        if (!empty($metaNoRouteDirectives)) {
            return $metaNoRouteDirectives;
        }

        return null;
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
