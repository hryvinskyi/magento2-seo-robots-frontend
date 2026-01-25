<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Integration\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredUrls;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredUrlsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class IsIgnoredUrlsTest extends TestCase
{
    private ?IsIgnoredUrlsInterface $model = null;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(IsIgnoredUrlsInterface::class);
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testInterfacePreferenceIsConfigured(): void
    {
        $this->assertInstanceOf(IsIgnoredUrls::class, $this->model);
    }
}
