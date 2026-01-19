<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoApi\Api\CheckPatternInterface;
use Hryvinskyi\SeoApi\Api\GetBaseUrlInterface;
use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Serialize\SerializerInterface;

class GetRobotsByRequest implements GetRobotsByRequestInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly RobotsListInterface $robotsList,
        private readonly CheckPatternInterface $checkPattern,
        private readonly GetBaseUrlInterface $baseUrl,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request): ?string
    {
        $fullAction = $request->getFullActionName();
        $robots = $this->config->getMetaRobots();

        // Sort by priority (highest first)
        usort($robots, static function ($a, $b) {
            return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
        });

        foreach ($robots as $robot) {
            if ($this->checkPattern->execute($fullAction, $robot['pattern'] ?? '')
                || $this->checkPattern->execute($this->baseUrl->execute(), $robot['pattern'] ?? '')
            ) {
                // Use new directive array format instead of legacy 'option' code
                $directives = $robot['meta_directives'] ?? [];
                if (!empty($directives)) {
                    // Unserialize if stored as string
                    if (is_string($directives)) {
                        $directives = $this->serializer->unserialize($directives);
                    }
                    if (is_array($directives)) {
                        // Check if structured format (array of arrays with 'value' key)
                        $firstElement = reset($directives);
                        if (is_array($firstElement) && isset($firstElement['value'])) {
                            return $this->robotsList->buildFromStructuredDirectives($directives);
                        }
                        return $this->robotsList->buildMetaRobotsFromDirectives($directives);
                    }
                }
            }
        }

        return null;
    }
}
