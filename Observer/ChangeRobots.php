<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Observer;

use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobotsInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config as PageConfig;

class ChangeRobots implements ObserverInterface
{
    public function __construct(
        private readonly ApplyRobotsInterface $applyRobots,
        private readonly PageConfig $pageConfig,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer): void
    {
        if ($this->request instanceof HttpRequestInterface) {
            $this->applyRobots->execute($this->request, $this->pageConfig);
        }
    }
}
