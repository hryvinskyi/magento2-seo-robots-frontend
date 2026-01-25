<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoApi\Api\CheckPatternInterface;
use Hryvinskyi\SeoApi\Api\GetBaseUrlInterface;
use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\GetXRobotsByRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\Parameters;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetXRobotsByRequestTest extends TestCase
{
    private GetXRobotsByRequest $model;
    private ConfigInterface|MockObject $configMock;
    private CheckPatternInterface|MockObject $checkPatternMock;
    private GetBaseUrlInterface|MockObject $baseUrlMock;
    private RequestInterface|MockObject $requestMock;
    private StoreManagerInterface|MockObject $storeManagerMock;
    private StoreInterface|MockObject $storeMock;
    private Parameters|MockObject $queryMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->checkPatternMock = $this->createMock(CheckPatternInterface::class);
        $this->baseUrlMock = $this->createMock(GetBaseUrlInterface::class);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getControllerName', 'getQuery', 'getFullActionName'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['isCurrentlySecure'])
            ->getMockForAbstractClass();
        $this->queryMock = $this->createMock(Parameters::class);

        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);

        $this->requestMock->method('getQuery')
            ->willReturn($this->queryMock);

        $this->model = new GetXRobotsByRequest(
            $this->configMock,
            $this->checkPatternMock,
            $this->baseUrlMock,
            $this->requestMock,
            $this->storeManagerMock
        );
    }

    public function testExecuteReturnsHttpsDirectivesForSecureConnection(): void
    {
        $httpsDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->storeMock->expects($this->once())
            ->method('isCurrentlySecure')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getHttpsXRobotsDirectives')
            ->willReturn($httpsDirectives);

        $this->assertEquals($httpsDirectives, $this->model->execute());
    }

    public function testExecuteReturnsNoRouteDirectivesFor404Page(): void
    {
        $noRouteDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->storeMock->method('isCurrentlySecure')
            ->willReturn(false);

        $this->configMock->method('getHttpsXRobotsDirectives')
            ->willReturn([]);

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('noroute');

        $this->configMock->expects($this->once())
            ->method('getNoRouteXRobotsTypes')
            ->willReturn($noRouteDirectives);

        $this->assertEquals($noRouteDirectives, $this->model->execute());
    }

    public function testExecuteFallsBackToMetaRobotsFor404WhenNoXRobotsSet(): void
    {
        $metaNoRouteDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->storeMock->method('isCurrentlySecure')
            ->willReturn(false);

        $this->configMock->method('getHttpsXRobotsDirectives')
            ->willReturn([]);

        $this->requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('noroute');

        $this->configMock->method('getNoRouteXRobotsTypes')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getNoRouteRobotsTypes')
            ->willReturn($metaNoRouteDirectives);

        $this->assertEquals($metaNoRouteDirectives, $this->model->execute());
    }

    public function testExecuteReturnsPaginatedDirectivesForPage2(): void
    {
        $paginatedDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '2']);

        $this->configMock->expects($this->once())
            ->method('isXRobotsPaginatedEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedXRobots')
            ->willReturn($paginatedDirectives);

        $this->assertEquals($paginatedDirectives, $this->model->execute());
    }

    public function testExecuteFallsBackToMetaRobotsPaginatedWhenNoXRobotsPaginated(): void
    {
        $metaPaginatedDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '2']);

        $this->configMock->method('isXRobotsPaginatedEnabled')
            ->willReturn(true);

        $this->configMock->method('getPaginatedXRobots')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('isPaginatedRobots')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedMetaRobots')
            ->willReturn($metaPaginatedDirectives);

        $this->assertEquals($metaPaginatedDirectives, $this->model->execute());
    }

    public function testExecuteReturnsPaginatedFilteredDirectives(): void
    {
        $filteredDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '2', 'color' => 'red']);

        $this->configMock->expects($this->once())
            ->method('isXRobotsPaginatedFilteredEnabled')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedFilteredXRobots')
            ->willReturn($filteredDirectives);

        $this->assertEquals($filteredDirectives, $this->model->execute());
    }

    public function testExecuteFallsBackToMetaRobotsPaginatedFilteredWhenNoXRobots(): void
    {
        $metaFilteredDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '3', 'size' => 'large']);

        $this->configMock->method('isXRobotsPaginatedFilteredEnabled')
            ->willReturn(true);

        $this->configMock->method('getPaginatedFilteredXRobots')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('isPaginatedFilteredRobots')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedFilteredMetaRobots')
            ->willReturn($metaFilteredDirectives);

        $this->assertEquals($metaFilteredDirectives, $this->model->execute());
    }

    public function testExecuteReturnsEmptyArrayWhenNoRulesConfigured(): void
    {
        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getXRobotsRules')
            ->willReturn([]);

        $this->assertEquals([], $this->model->execute());
    }

    public function testExecuteReturnsMatchedXRobotsRule(): void
    {
        $xrobotsDirectives = ['noindex', 'nofollow'];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getXRobotsRules')
            ->willReturn([
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'xrobots_directives' => $xrobotsDirectives,
                ],
            ]);

        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->with('catalog_product_view', 'catalog_product_*')
            ->willReturn(true);

        $this->assertEquals($xrobotsDirectives, $this->model->execute());
    }

    public function testExecuteIgnoresPage1(): void
    {
        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '1']);

        $this->configMock->expects($this->once())
            ->method('getXRobotsRules')
            ->willReturn([]);

        $this->assertEquals([], $this->model->execute());
    }

    public function testExecuteSortsRulesByPriorityDescending(): void
    {
        $highPriorityDirectives = ['noindex'];
        $lowPriorityDirectives = ['index'];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getXRobotsRules')
            ->willReturn([
                [
                    'pattern' => 'catalog_*',
                    'priority' => 5,
                    'xrobots_directives' => $lowPriorityDirectives,
                ],
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'xrobots_directives' => $highPriorityDirectives,
                ],
            ]);

        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->with('catalog_product_view', 'catalog_product_*')
            ->willReturn(true);

        $this->assertEquals($highPriorityDirectives, $this->model->execute());
    }

    public function testExecuteMatchesUrlPattern(): void
    {
        $xrobotsDirectives = ['noarchive'];

        $this->setupNonSecureNonNoRouteMocks();

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->configMock->expects($this->once())
            ->method('getXRobotsRules')
            ->willReturn([
                [
                    'pattern' => '*product.html',
                    'priority' => 10,
                    'xrobots_directives' => $xrobotsDirectives,
                ],
            ]);

        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->baseUrlMock->expects($this->once())
            ->method('execute')
            ->willReturn('https://example.com/product.html');

        $this->checkPatternMock->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertEquals($xrobotsDirectives, $this->model->execute());
    }

    private function setupNonSecureNonNoRouteMocks(): void
    {
        $this->storeMock->method('isCurrentlySecure')
            ->willReturn(false);

        $this->configMock->method('getHttpsXRobotsDirectives')
            ->willReturn([]);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');
    }
}
