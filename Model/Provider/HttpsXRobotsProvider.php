<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model\Provider;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\XRobotsProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides X-Robots-Tag directives for HTTPS requests
 */
class HttpsXRobotsProvider implements XRobotsProviderInterface
{
    /**
     * Default priority for HTTPS (highest to override all other sources)
     */
    private const DEFAULT_PRIORITY = 15000;

    public function __construct(
        private readonly ConfigInterface $config,
        private readonly StoreManagerInterface $storeManager,
        private readonly int $sortOrder = 5,
        private readonly int $priority = self::DEFAULT_PRIORITY
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getDirectives(RequestInterface $request): ?array
    {
        if (!$this->storeManager->getStore()->isCurrentlySecure()) {
            return null;
        }

        $httpsDirectives = $this->config->getHttpsXRobotsDirectives();

        if (empty($httpsDirectives)) {
            return null;
        }

        return $httpsDirectives;
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
        return $this->priority;
    }
}
