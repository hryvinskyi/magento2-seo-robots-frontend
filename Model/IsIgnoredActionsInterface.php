<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;

interface IsIgnoredActionsInterface
{
    /**
     * @param HttpRequestInterface $request
     *
     * @return bool
     */
    public function execute(HttpRequestInterface $request): bool;
}
