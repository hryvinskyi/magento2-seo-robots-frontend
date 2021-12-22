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
    /**
     * @var ApplyRobotsInterface
     */
    private $applyRobots;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ChangeRobots constructor.
     *
     * @param ApplyRobotsInterface $applyRobots
     * @param PageConfig $pageConfig
     * @param RequestInterface $request
     */
    public function __construct(ApplyRobotsInterface $applyRobots, PageConfig $pageConfig, RequestInterface $request)
    {
        $this->applyRobots = $applyRobots;
        $this->pageConfig = $pageConfig;
        $this->request = $request;
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
