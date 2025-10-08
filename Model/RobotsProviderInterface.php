<?php
/**
 * Copyright (c) 2025. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;

/**
 * Interface for robots meta tag providers
 *
 * Implement this interface to provide custom robots meta tags based on specific conditions
 * Providers are executed in order of their sortOrder
 */
interface RobotsProviderInterface
{
    /**
     * Get robots meta tag value
     *
     * @param HttpRequestInterface $request
     * @return string|null Return robots meta tag string (e.g., "NOINDEX,NOFOLLOW") or null if not applicable
     */
    public function getRobots(HttpRequestInterface $request): ?string;

    /**
     * Get the sort order for this provider
     * Lower numbers execute first
     *
     * @return int
     */
    public function getSortOrder(): int;
}
