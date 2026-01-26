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
 * Provides X-Robots-Tag directives for paginated pages
 */
class PaginationXRobotsProvider implements XRobotsProviderInterface
{
    /**
     * Default priority for pagination (high to override category/product)
     */
    private const DEFAULT_PRIORITY = 10000;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly int $sortOrder = 30,
        private readonly int $priority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDirectives(RequestInterface $request): ?array
    {
        $queryParams = $request->getQuery()->toArray();

        if (!isset($queryParams['p'])) {
            return null;
        }

        // Pagination only (no filters) - 'p' is the only query parameter
        if (count($queryParams) === 1 && $this->config->isXRobotsPaginatedEnabled()) {
            $paginatedDirectives = $this->config->getPaginatedXRobots();
            if (!empty($paginatedDirectives)) {
                return $paginatedDirectives;
            }

            // Fall back to meta robots paginated directives
            if ($this->config->isPaginatedRobots()) {
                $metaPaginatedDirectives = $this->config->getPaginatedMetaRobots();
                if (!empty($metaPaginatedDirectives)) {
                    return $metaPaginatedDirectives;
                }
            }
        }

        // Pagination with filters - 'p' plus other query parameters
        if (count($queryParams) > 1 && $this->config->isXRobotsPaginatedFilteredEnabled()) {
            $filteredDirectives = $this->config->getPaginatedFilteredXRobots();
            if (!empty($filteredDirectives)) {
                return $filteredDirectives;
            }

            // Fall back to meta robots paginated filtered directives
            if ($this->config->isPaginatedFilteredRobots()) {
                $metaFilteredDirectives = $this->config->getPaginatedFilteredMetaRobots();
                if (!empty($metaFilteredDirectives)) {
                    return $metaFilteredDirectives;
                }
            }
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
