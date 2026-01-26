<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model\Provider;

use Hryvinskyi\SeoApi\Api\CheckPatternInterface;
use Hryvinskyi\SeoApi\Api\GetBaseUrlInterface;
use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\XRobotsProviderInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Provides X-Robots-Tag directives based on URL pattern configuration
 */
class UrlPatternXRobotsProvider implements XRobotsProviderInterface
{
    /**
     * Default priority for URL pattern provider
     */
    private const DEFAULT_PRIORITY = 500;

    /**
     * @var int|null Cached priority from last getDirectives call
     */
    private ?int $lastPriority = null;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly CheckPatternInterface $checkPattern,
        private readonly GetBaseUrlInterface $baseUrl,
        private readonly int $sortOrder = 10,
        private readonly int $defaultPriority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDirectives(RequestInterface $request): ?array
    {
        $xrobotsRules = $this->config->getXRobotsRules();

        if (empty($xrobotsRules)) {
            $this->lastPriority = null;
            return null;
        }

        $fullAction = $request->getFullActionName();

        // Sort by priority (highest first)
        usort($xrobotsRules, static function ($a, $b) {
            return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
        });

        foreach ($xrobotsRules as $rule) {
            if ($this->checkPattern->execute($fullAction, $rule['pattern'] ?? '')
                || $this->checkPattern->execute($this->baseUrl->execute(), $rule['pattern'] ?? '')
            ) {
                $directives = $rule['xrobots_directives'] ?? [];
                if (!empty($directives)) {
                    $this->lastPriority = (int)($rule['priority'] ?? $this->defaultPriority);
                    return $directives;
                }
            }
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
