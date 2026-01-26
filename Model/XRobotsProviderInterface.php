<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\RequestInterface;

/**
 * Interface for X-Robots-Tag HTTP header providers
 *
 * Implement this interface to provide custom X-Robots-Tag directives based on specific conditions.
 * Providers are executed in order of their sortOrder, but the final directives are determined
 * by priority - highest priority wins regardless of execution order.
 */
interface XRobotsProviderInterface
{
    /**
     * Get X-Robots-Tag directives
     *
     * @param RequestInterface $request
     * @return array<array{value: string}>|null Return array of directives or null if not applicable
     */
    public function getDirectives(RequestInterface $request): ?array;

    /**
     * Get the sort order for this provider
     *
     * Lower numbers execute first. This determines the order in which providers are evaluated.
     *
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Get the priority for this provider
     *
     * Higher numbers have higher priority and will override lower priority results.
     * When multiple providers return directives, the one with highest priority wins.
     *
     * Recommended priority scale:
     * - URL patterns: configurable per pattern (default varies)
     * - Category page: 1000
     * - Product page: 2000
     * - NoRoute (404): 5000
     * - Pagination: 10000
     * - HTTPS: 15000
     *
     * @return int
     */
    public function getPriority(): int;
}
