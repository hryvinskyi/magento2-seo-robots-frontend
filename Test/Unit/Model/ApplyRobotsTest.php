<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobots;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredActionsInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredUrlsInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\RobotsProviderInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\View\Page\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyRobotsTest extends TestCase
{
    private ApplyRobots $model;
    private IsIgnoredActionsInterface|MockObject $ignoredActionsMock;
    private IsIgnoredUrlsInterface|MockObject $isIgnoredUrlsMock;
    private ConfigInterface|MockObject $configMock;
    private HttpRequestInterface|MockObject $requestMock;
    private Config|MockObject $pageConfigMock;

    protected function setUp(): void
    {
        $this->ignoredActionsMock = $this->createMock(IsIgnoredActionsInterface::class);
        $this->isIgnoredUrlsMock = $this->createMock(IsIgnoredUrlsInterface::class);
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->requestMock = $this->createMock(HttpRequestInterface::class);
        $this->pageConfigMock = $this->createMock(Config::class);

        $this->model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
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

    public function testExecuteAppliesHighestPriorityProvider(): void
    {
        $provider1Mock = $this->createMock(RobotsProviderInterface::class);
        $provider2Mock = $this->createMock(RobotsProviderInterface::class);

        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(200);

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

        // Higher priority (200) wins, so NOFOLLOW should be set
        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOFOLLOW');

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteSelectsHigherPriorityEvenWithLowerSortOrder(): void
    {
        $provider1Mock = $this->createMock(RobotsProviderInterface::class);
        $provider2Mock = $this->createMock(RobotsProviderInterface::class);

        // Provider 1 executes first (lower sortOrder) but has lower priority
        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(1000);
        // Provider 2 executes second but has higher priority
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(2000);

        $provider1Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn('CATEGORY_ROBOTS');

        $provider2Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn('PRODUCT_ROBOTS');

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->configMock,
            [$provider1Mock, $provider2Mock]
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

        // Higher priority wins regardless of sort order
        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('PRODUCT_ROBOTS');

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteSkipsProviderReturningNull(): void
    {
        $provider1Mock = $this->createMock(RobotsProviderInterface::class);
        $provider2Mock = $this->createMock(RobotsProviderInterface::class);

        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(200);

        $provider1Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn(null);

        $provider2Mock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn('NOFOLLOW');

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->configMock,
            [$provider1Mock, $provider2Mock]
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

        // Only provider2 returns a value
        $this->pageConfigMock->expects($this->once())
            ->method('setRobots')
            ->with('NOFOLLOW');

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteDoesNotSetRobotsWhenAllProvidersReturnNull(): void
    {
        $providerMock = $this->createMock(RobotsProviderInterface::class);
        $providerMock->method('getSortOrder')->willReturn(10);
        $providerMock->method('getPriority')->willReturn(100);

        $providerMock->expects($this->once())
            ->method('getRobots')
            ->with($this->requestMock)
            ->willReturn(null);

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
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

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testExecuteWithNoProviders(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->isIgnoredUrlsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->ignoredActionsMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->pageConfigMock->expects($this->never())
            ->method('setRobots');

        $this->model->execute($this->requestMock, $this->pageConfigMock);
    }

    public function testProvidersAreSortedBySortOrder(): void
    {
        $provider1Mock = $this->createMock(RobotsProviderInterface::class);
        $provider2Mock = $this->createMock(RobotsProviderInterface::class);
        $provider3Mock = $this->createMock(RobotsProviderInterface::class);

        // Providers added in random order
        $provider1Mock->method('getSortOrder')->willReturn(30);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(10);
        $provider2Mock->method('getPriority')->willReturn(100);
        $provider3Mock->method('getSortOrder')->willReturn(20);
        $provider3Mock->method('getPriority')->willReturn(100);

        $executionOrder = [];

        $provider1Mock->method('getRobots')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider1';
                return null;
            });

        $provider2Mock->method('getRobots')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider2';
                return null;
            });

        $provider3Mock->method('getRobots')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider3';
                return null;
            });

        $model = new ApplyRobots(
            $this->ignoredActionsMock,
            $this->isIgnoredUrlsMock,
            $this->configMock,
            [$provider1Mock, $provider2Mock, $provider3Mock]
        );

        $this->configMock->method('isEnabled')->willReturn(true);
        $this->isIgnoredUrlsMock->method('execute')->willReturn(false);
        $this->ignoredActionsMock->method('execute')->willReturn(false);

        $model->execute($this->requestMock, $this->pageConfigMock);

        // Should execute in sort order: provider2 (10), provider3 (20), provider1 (30)
        $this->assertEquals(['provider2', 'provider3', 'provider1'], $executionOrder);
    }
}
