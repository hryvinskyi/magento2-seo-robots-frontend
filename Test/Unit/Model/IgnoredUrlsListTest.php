<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IgnoredUrlsList;
use PHPUnit\Framework\TestCase;

class IgnoredUrlsListTest extends TestCase
{
    public function testGetListReturnsEmptyArrayByDefault(): void
    {
        $model = new IgnoredUrlsList();
        $this->assertEquals([], $model->getList());
    }

    public function testGetListReturnsInjectedArray(): void
    {
        $ignoredUrls = [
            'checkout/',
            'onestepcheckout',
        ];

        $model = new IgnoredUrlsList($ignoredUrls);
        $this->assertEquals($ignoredUrls, $model->getList());
    }

    public function testGetListReturnsSameArrayMultipleTimes(): void
    {
        $ignoredUrls = ['checkout/', 'cart/'];
        $model = new IgnoredUrlsList($ignoredUrls);

        $this->assertEquals($ignoredUrls, $model->getList());
        $this->assertEquals($ignoredUrls, $model->getList());
        $this->assertSame($model->getList(), $model->getList());
    }
}
