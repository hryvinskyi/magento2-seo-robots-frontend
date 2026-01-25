<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IgnoredActionsList;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredActions;
use Magento\Framework\App\HttpRequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IsIgnoredActionsTest extends TestCase
{
    private IsIgnoredActions $model;
    private IgnoredActionsList|MockObject $ignoredActionsListMock;
    private HttpRequestInterface|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->ignoredActionsListMock = $this->createMock(IgnoredActionsList::class);
        $this->requestMock = $this->getMockBuilder(HttpRequestInterface::class)
            ->addMethods(['getModuleName', 'getControllerName', 'getActionName'])
            ->getMockForAbstractClass();

        $this->model = new IsIgnoredActions($this->ignoredActionsListMock);
    }

    public function testExecuteReturnsTrueForAjaxRequest(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);

        $this->assertTrue($this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsTrueForIgnoredAction(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

        $this->requestMock->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Review');

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('Product');

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn('ListAjax');

        $this->ignoredActionsListMock->expects($this->once())
            ->method('getList')
            ->willReturn(['review_product_listajax', 'customer_address_form']);

        $this->assertTrue($this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsFalseForNonIgnoredAction(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

        $this->requestMock->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Catalog');

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('Product');

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn('View');

        $this->ignoredActionsListMock->expects($this->once())
            ->method('getList')
            ->willReturn(['review_product_listajax', 'customer_address_form']);

        $this->assertFalse($this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsFalseWhenIgnoredListIsEmpty(): void
    {
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(false);

        $this->requestMock->expects($this->once())
            ->method('getModuleName')
            ->willReturn('Catalog');

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('Product');

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn('View');

        $this->ignoredActionsListMock->expects($this->once())
            ->method('getList')
            ->willReturn([]);

        $this->assertFalse($this->model->execute($this->requestMock));
    }

    /**
     * @dataProvider fullActionCodeDataProvider
     *
     * @param string $moduleName
     * @param string $controllerName
     * @param string $actionName
     * @param string $expectedCode
     */
    public function testGetFullActionCodeReturnsLowercaseCode(
        string $moduleName,
        string $controllerName,
        string $actionName,
        string $expectedCode
    ): void {
        $this->requestMock->expects($this->once())
            ->method('getModuleName')
            ->willReturn($moduleName);

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn($controllerName);

        $this->requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn($actionName);

        $this->assertEquals($expectedCode, $this->model->getFullActionCode($this->requestMock));
    }

    /**
     * @return array<string, array{moduleName: string, controllerName: string, actionName: string, expectedCode: string}>
     */
    public static function fullActionCodeDataProvider(): array
    {
        return [
            'all lowercase' => [
                'moduleName' => 'catalog',
                'controllerName' => 'product',
                'actionName' => 'view',
                'expectedCode' => 'catalog_product_view',
            ],
            'mixed case' => [
                'moduleName' => 'Catalog',
                'controllerName' => 'Product',
                'actionName' => 'View',
                'expectedCode' => 'catalog_product_view',
            ],
            'all uppercase' => [
                'moduleName' => 'CATALOG',
                'controllerName' => 'PRODUCT',
                'actionName' => 'VIEW',
                'expectedCode' => 'catalog_product_view',
            ],
            'customer address form' => [
                'moduleName' => 'Customer',
                'controllerName' => 'Address',
                'actionName' => 'Form',
                'expectedCode' => 'customer_address_form',
            ],
        ];
    }
}
