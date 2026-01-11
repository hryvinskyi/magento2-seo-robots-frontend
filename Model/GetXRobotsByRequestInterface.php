<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

/**
 * Interface for getting X-Robots-Tag directives by request
 */
interface GetXRobotsByRequestInterface
{
    /**
     * Get X-Robots-Tag directives for current request
     *
     * @return array Array of directives
     */
    public function execute(): array;
}
