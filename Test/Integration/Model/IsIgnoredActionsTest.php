<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Integration\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredActions;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredActionsInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class IsIgnoredActionsTest extends TestCase
{
    private ?IsIgnoredActionsInterface $model = null;

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(IsIgnoredActionsInterface::class);
    }

    protected function tearDown(): void
    {
        $this->model = null;
    }

    public function testInterfacePreferenceIsConfigured(): void
    {
        $this->assertInstanceOf(IsIgnoredActions::class, $this->model);
    }

    public function testDefaultIgnoredActionsAreConfigured(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var HttpRequest $request */
        $request = $objectManager->create(HttpRequest::class);
        $request->setModuleName('review');
        $request->setControllerName('product');
        $request->setActionName('listAjax');

        $this->assertTrue($this->model->execute($request));
    }

    public function testNonIgnoredActionReturnsFalse(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var HttpRequest $request */
        $request = $objectManager->create(HttpRequest::class);
        $request->setModuleName('catalog');
        $request->setControllerName('product');
        $request->setActionName('view');

        $this->assertFalse($this->model->execute($request));
    }
}
