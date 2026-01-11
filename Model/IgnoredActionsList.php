<?php
/**
 * Copyright (c) 2021-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

class IgnoredActionsList
{
    public function __construct(
        private readonly array $list = []
    ) {
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }
}
