<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

/**
 * Value object representing X-Robots-Tag directives result with its priority and source
 */
class XRobotsResult
{
    /**
     * @param array<array{value: string}> $directives The X-Robots-Tag directives
     * @param int $priority The priority of this result (higher wins)
     * @param string $source The source/provider name for debugging purposes
     */
    public function __construct(
        private readonly array $directives,
        private readonly int $priority,
        private readonly string $source
    ) {
    }

    /**
     * Get the X-Robots-Tag directives
     *
     * @return array<array{value: string}>
     */
    public function getDirectives(): array
    {
        return $this->directives;
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
