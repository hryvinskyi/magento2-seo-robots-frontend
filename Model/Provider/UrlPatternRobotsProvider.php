<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model\Provider;

use Hryvinskyi\SeoRobotsFrontend\Model\GetRobotsByRequestInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\RobotsProviderInterface;
use Magento\Framework\App\HttpRequestInterface;

/**
 * Provides robots meta tags based on URL pattern configuration
 */
class UrlPatternRobotsProvider implements RobotsProviderInterface
{
    /**
     * Default priority for URL pattern provider
     */
    private const DEFAULT_PRIORITY = 500;

    /**
     * @var int|null Cached priority from last getRobots call
     */
    private ?int $lastPriority = null;

    public function __construct(
        private readonly GetRobotsByRequestInterface $getRobotsByRequest,
        private readonly int $sortOrder = 10,
        private readonly int $defaultPriority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRobots(HttpRequestInterface $request): ?string
    {
        $result = $this->getRobotsByRequest->executeWithPriority($request);

        if ($result !== null) {
            $this->lastPriority = $result->getPriority();
            return $result->getRobots();
        }

        $this->lastPriority = null;
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
        return $this->lastPriority ?? $this->defaultPriority;
    }
}
