<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoApi\Api\CheckPatternInterface;
use Hryvinskyi\SeoApi\Api\GetBaseUrlInterface;
use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get X-Robots-Tag directives based on independent rules
 */
class GetXRobotsByRequest implements GetXRobotsByRequestInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly CheckPatternInterface $checkPattern,
        private readonly GetBaseUrlInterface $baseUrl,
        private readonly RequestInterface $request,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        // Check HTTPS X-Robots directives first
        if ($this->storeManager->getStore()->isCurrentlySecure()) {
            $httpsDirectives = $this->config->getHttpsXRobotsDirectives();
            if (!empty($httpsDirectives)) {
                return $httpsDirectives;
            }
        }

        // Check for 404 pages (noroute controller)
        if ($this->request->getControllerName() === 'noroute') {
            $noRouteDirectives = $this->config->getNoRouteXRobotsTypes();
            if (!empty($noRouteDirectives)) {
                return $noRouteDirectives;
            }
            // Fall back to meta robots 404 directives if no specific X-Robots set
            $metaNoRouteDirectives = $this->config->getNoRouteRobotsTypes();
            if (!empty($metaNoRouteDirectives)) {
                return $metaNoRouteDirectives;
            }
        }

        // Check for paginated content using URL query params directly
        $queryParams = $this->request->getQuery()->toArray();
        if (isset($queryParams['p']) && (int)$queryParams['p'] > 1) {
            // Pagination only (no filters) - 'p' is the only query parameter
            if (count($queryParams) === 1 && $this->config->isXRobotsPaginatedEnabled()) {
                $paginatedDirectives = $this->config->getPaginatedXRobots();
                if (!empty($paginatedDirectives)) {
                    return $paginatedDirectives;
                }
                // Fall back to meta robots paginated directives if no specific X-Robots set
                if ($this->config->isPaginatedRobots()) {
                    $metaPaginatedDirectives = $this->config->getPaginatedMetaRobots();
                    if (!empty($metaPaginatedDirectives)) {
                        return $metaPaginatedDirectives;
                    }
                }
            }
            // Pagination with filters - 'p' plus other query parameters
            elseif (count($queryParams) > 1 && $this->config->isXRobotsPaginatedFilteredEnabled()) {
                $filteredDirectives = $this->config->getPaginatedFilteredXRobots();
                if (!empty($filteredDirectives)) {
                    return $filteredDirectives;
                }
                // Fall back to meta robots paginated filtered directives if no specific X-Robots set
                if ($this->config->isPaginatedFilteredRobots()) {
                    $metaFilteredDirectives = $this->config->getPaginatedFilteredMetaRobots();
                    if (!empty($metaFilteredDirectives)) {
                        return $metaFilteredDirectives;
                    }
                }
            }
        }


        // Get independent X-Robots rules
        $xrobotsRules = $this->config->getXRobotsRules();

        if (empty($xrobotsRules)) {
            return [];
        }

        $fullAction = $this->request->getFullActionName();

        // Sort by priority (highest first)
        usort($xrobotsRules, static function ($a, $b) {
            return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
        });

        foreach ($xrobotsRules as $rule) {
            if ($this->checkPattern->execute($fullAction, $rule['pattern'] ?? '')
                || $this->checkPattern->execute($this->baseUrl->execute(), $rule['pattern'] ?? '')
            ) {
                return $rule['xrobots_directives'] ?? [];
            }
        }

        return [];
    }
}
