<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Integration\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobots;
use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobotsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class ApplyRobotsTest extends TestCase
{
    private ?ApplyRobotsInterface $model = null;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(ApplyRobotsInterface::class);
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testInterfacePreferenceIsConfigured(): void
    {
        $this->assertInstanceOf(ApplyRobots::class, $this->model);
    }
}
