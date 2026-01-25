<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobots;
use Hryvinskyi\SeoRobotsFrontend\Model\GetRobotsByRequestInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredActionsInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredUrlsInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\RobotsProviderInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Stdlib\Parameters;
use Magento\Framework\View\Page\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyRobotsTest extends TestCase
{
    private ApplyRobots $model;
    private IsIgnoredActionsInterface|MockObject $ignoredActionsMock;
    private IsIgnoredUrlsInterface|MockObject $isIgnoredUrlsMock;
    private GetRobotsByRequestInterface|MockObject $getRobotsByRequestMock;
    private RobotsListInterface|MockObject $robotsListMock;
    private ConfigInterface|MockObject $configMock;
    private HttpRequestInterface|MockObject $requestMock;
    private Config|MockObject $pageConfigMock;
    private Parameters|MockObject $queryMock;

    protected function setUp(): void
    {
        $this->ignoredActionsMock = $this->createMock(IsIgnoredActionsInterface::class);
        $this->isIgnoredUrlsMock = $this->createMock(IsIgnoredUrlsInterface::class);
        $this->getRobotsByRequestMock = $this->createMock(GetRobotsByRequestInterface::class);
        $this->robotsListMock = $this->createMock(RobotsListInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->requestMock = $this->getMockBuilder(HttpRequestInterface::class)
            ->addMethods(['getControllerName', 'getQuery'])
            ->getMockForAbstractClass();
        $this->pageConfigMock = $this->createMock(Config::class);
        $this->queryMock = $this->createMock(Parameters::class);

        $this->requestMock->method('getQuery')
            ->willReturn($this->queryMock);

        $this->model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->getRobotsByRequestMock,
            $this->robotsListMock,
            $this->configMock
        );
    }

    public function testExecuteDoesNothingWhenModuleIsDisabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteDoesNothingWhenUrlIsIgnored(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteDoesNothingWhenActionIsIgnored(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->ignoredActionsMock->expects($this->once())
            ->method('execute')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteAppliesRobotsFromGetRobotsByRequest(): void
    {
        $this->setupEnabledNonIgnoredMocks();

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->with($this->requestMock)
            ->willReturn('NOINDEX, NOFOLLOW');

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOINDEX, NOFOLLOW');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteAppliesNoRouteRobotsFor404Page(): void
    {
        $this->setupEnabledNonIgnoredMocks();

        $noRouteDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('noroute');

        $this->configMock->expects($this->once())
            ->method('getNoRouteRobotsTypes')
            ->willReturn($noRouteDirectives);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($noRouteDirectives)
            ->willReturn('NOINDEX');

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOINDEX');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteAppliesPaginatedRobotsForPage2(): void
    {
        $this->setupEnabledNonIgnoredMocks();

        $paginatedDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '2']);

        $this->configMock->expects($this->once())
            ->method('isPaginatedRobots')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedMetaRobots')
            ->willReturn($paginatedDirectives);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($paginatedDirectives)
            ->willReturn('NOINDEX');

        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOINDEX');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteAppliesPaginatedFilteredRobots(): void
    {
        $this->setupEnabledNonIgnoredMocks();

        $filteredDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '2', 'color' => 'red']);

        $this->configMock->expects($this->once())
            ->method('isPaginatedFilteredRobots')
            ->willReturn(true);

        $this->configMock->expects($this->once())
            ->method('getPaginatedFilteredMetaRobots')
            ->willReturn($filteredDirectives);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($filteredDirectives)
            ->willReturn('NOINDEX');

        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOINDEX');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteIgnoresPage1(): void
    {
        $this->setupEnabledNonIgnoredMocks();

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn(['p' => '1']);

        $this->configMock->expects($this->once())
            ->method('isPaginatedRobots');

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteAppliesRobotsProviders(): void
    {
        $provider1Mock = $this->createMock(RobotsProviderInterface::class);
        $provider2Mock = $this->createMock(RobotsProviderInterface::class);

        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider2Mock->method('getSortOrder')->willReturn(20);

        $provider1Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn('NOINDEX');

        $provider2Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn('NOFOLLOW');

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->getRobotsByRequestMock,
            $this->robotsListMock,
            $this->configMock,
            [$provider2Mock, $provider1Mock]
        );

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->ignoredActionsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->exactly(2))
            ->method('setRobots')
            ->willReturnCallback(function ($robots) {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertEquals('NOINDEX', $robots);
                } else {
                    $this->assertEquals('NOFOLLOW', $robots);
                }
            });

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteSkipsProviderReturningNull(): void
    {
        $providerMock = $this->createMock(RobotsProviderInterface::class);
        $providerMock->method('getSortOrder')->willReturn(10);

        $providerMock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn(null);

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->getRobotsByRequestMock,
            $this->robotsListMock,
            $this->configMock,
            [$providerMock]
        );

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->ignoredActionsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->getRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn(null);

        $this->requestMock->method('getControllerName')
            ->willReturn('category');

        $this->configMock->method('getNoRouteRobotsTypes')
            ->willReturn([]);

        $this->queryMock->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    private function setupEnabledNonIgnoredMocks(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->ignoredActionsMock->expects($this->once())
            ->method('execute')
            ->with($this->requestMock)
            ->willReturn(false);
    }
}
