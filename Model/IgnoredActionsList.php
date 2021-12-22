<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

class IgnoredActionsList
{
    /**
     * @var array
     */
    private $list;

    /**
     * @param array $list
     */
    public function __construct(
        array $list = []
    ) {
        $this->list = $list;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }
}
