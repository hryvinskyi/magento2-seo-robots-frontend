<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Observer;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Psr\Log\LoggerInterface;

class AddXRobotsHeader implements ObserverInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigInterface $config
     * @param PageConfig $pageConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        PageConfig $pageConfig,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->pageConfig = $pageConfig;
        $this->logger = $logger;
    }

    /**
     * Add X-Robots-Tag header to HTTP response
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isRobotsXheaderEnabled()) {
            return;
        }

        try {
            /** @var HttpResponse $response */
            $response = $observer->getEvent()->getData('response');

            if (!$response instanceof HttpResponse) {
                return;
            }

            // Get robots meta tag value from PageConfig (set by Hryvinskyi module)
            $robots = $this->pageConfig->getRobots();

            if (empty($robots)) {
                return;
            }

            // Add X-Robots-Tag header
            $response->setHeader('X-Robots-Tag', $robots, true);
        } catch (\Exception $e) {
            $this->logger->error(
                'Error adding X-Robots-Tag header: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
