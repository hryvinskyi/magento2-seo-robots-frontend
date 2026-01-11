<?php
/**
 * Copyright (c) 2026. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Observer;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\GetXRobotsByRequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Psr\Log\LoggerInterface;

class AddXRobotsHeader implements ObserverInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly PageConfig $pageConfig,
        private readonly LoggerInterface $logger,
        private readonly GetXRobotsByRequestInterface $getXRobotsByRequest,
        private readonly RobotsListInterface $robotsList
    ) {
    }

    /**
     * Add X-Robots-Tag header to HTTP response
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isRobotsXheaderEnabled()) {
            return;
        }

        try {
            /** @var HttpResponse $response */
            $response = $observer->getEvent()->getData('response');

            if (!$response instanceof HttpResponse) {
                return;
            }

            if ($response->isRedirect()) {
                return;
            }

            // Try to get independent X-Robots-Tag directives first
            $xrobotsDirectives = $this->getXRobotsByRequest->execute();

            // If no independent X-Robots directives, fallback to meta robots
            if (empty($xrobotsDirectives)) {
                $robots = $this->pageConfig->getRobots();
            } else {
                $robots = $this->robotsList->buildMetaRobotsFromDirectives($xrobotsDirectives);
            }

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
