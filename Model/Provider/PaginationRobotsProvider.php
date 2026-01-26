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
 * Provides robots meta tags for paginated pages
 */
class PaginationRobotsProvider implements RobotsProviderInterface
{
    /**
     * Default priority for pagination (highest to override category/product)
     */
    private const DEFAULT_PRIORITY = 10000;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly RobotsListInterface $robotsList,
        private readonly int $sortOrder = 30,
        private readonly int $priority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRobots(HttpRequestInterface $request): ?string
    {
        $queryParams = $request->getQuery()->toArray();

        if (!isset($queryParams['p'])) {
            return null;
        }

        // Pagination only (no filters) - 'p' is the only query parameter
        if (count($queryParams) === 1 && $this->config->isPaginatedRobots()) {
            $directives = $this->config->getPaginatedMetaRobots();
            return $this->robotsList->buildFromStructuredDirectives($directives);
        }

        // Pagination with filters - 'p' plus other query parameters
        if (count($queryParams) > 1 && $this->config->isPaginatedFilteredRobots()) {
            $directives = $this->config->getPaginatedFilteredMetaRobots();
            return $this->robotsList->buildFromStructuredDirectives($directives);
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
