<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Page\Config;

interface ApplyRobotsInterface
{
    /**
     * @param HttpRequestInterface $request
     * @param Config $pageConfig
     *
     * @throws LocalizedException
     */
    public function execute(HttpRequestInterface $request, Config $pageConfig): void;
}
