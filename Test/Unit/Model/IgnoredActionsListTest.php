<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IgnoredActionsList;
use PHPUnit\Framework\TestCase;

class IgnoredActionsListTest extends TestCase
{
    public function testGetListReturnsEmptyArrayByDefault(): void
    {
        $model = new IgnoredActionsList();
        $this->assertEquals([], $model->getList());
    }

    public function testGetListReturnsInjectedArray(): void
    {
        $ignoredActions = [
            'review_product_listajax',
            'customer_address_form',
            'customer_address_index',
        ];

        $model = new IgnoredActionsList($ignoredActions);
        $this->assertEquals($ignoredActions, $model->getList());
    }

    public function testGetListReturnsSameArrayMultipleTimes(): void
    {
        $ignoredActions = ['action_one', 'action_two'];
        $model = new IgnoredActionsList($ignoredActions);

        $this->assertEquals($ignoredActions, $model->getList());
        $this->assertEquals($ignoredActions, $model->getList());
        $this->assertSame($model->getList(), $model->getList());
    }
}
