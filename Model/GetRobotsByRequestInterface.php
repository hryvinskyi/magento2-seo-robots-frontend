<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;

interface GetRobotsByRequestInterface
{
    /**
     * Get robots value based on URL patterns
     *
     * @param HttpRequestInterface $request
     * @return string|null
     */
    public function execute(HttpRequestInterface $request): ?string;

    /**
     * Get robots result with priority from URL pattern configuration
     *
     * @param HttpRequestInterface $request
     * @return RobotsResult|null Returns RobotsResult with robots value and its configured priority, or null if no match
     */
    public function executeWithPriority(HttpRequestInterface $request): ?RobotsResult;
}
