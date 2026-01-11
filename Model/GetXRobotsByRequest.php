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
