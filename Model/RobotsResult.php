<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

/**
 * Value object representing a robots meta tag result with its priority and source
 */
class RobotsResult
{
    /**
     * @param string $robots The robots meta tag value (e.g., "NOINDEX,NOFOLLOW")
     * @param int $priority The priority of this result (higher wins)
     * @param string $source The source/provider name for debugging purposes
     */
    public function __construct(
        private readonly string $robots,
        private readonly int $priority,
        private readonly string $source
    ) {
    }

    /**
     * Get the robots meta tag value
     *
     * @return string
     */
    public function getRobots(): string
    {
        return $this->robots;
    }

    /**
     * Get the priority of this result
     *
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Get the source/provider name
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
}
