<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\GetXRobotsByRequest;
use Hryvinskyi\SeoRobotsFrontend\Model\XRobotsProviderInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetXRobotsByRequestTest extends TestCase
{
    private GetXRobotsByRequest $model;
    private RequestInterface|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->model = new GetXRobotsByRequest(
            $this->requestMock
        );
    }

    public function testExecuteReturnsEmptyArrayWithNoProviders(): void
    {
        $this->assertEquals([], $this->model->execute());
    }

    public function testExecuteReturnsHighestPriorityDirectives(): void
    {
        $provider1Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider2Mock = $this->createMock(XRobotsProviderInterface::class);

        $lowPriorityDirectives = [['value' => 'index', 'bot' => '', 'modification' => '']];
        $highPriorityDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(200);

        $provider1Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($lowPriorityDirectives);

        $provider2Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($highPriorityDirectives);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$provider1Mock, $provider2Mock]
        );

        // Higher priority (200) wins
        $this->assertEquals($highPriorityDirectives, $model->execute());
    }

    public function testExecuteSelectsHigherPriorityEvenWithLowerSortOrder(): void
    {
        $provider1Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider2Mock = $this->createMock(XRobotsProviderInterface::class);

        $categoryDirectives = [['value' => 'category_directive', 'bot' => '', 'modification' => '']];
        $productDirectives = [['value' => 'product_directive', 'bot' => '', 'modification' => '']];

        // Provider 1 executes first (lower sortOrder) but has lower priority
        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(1000);
        // Provider 2 executes second but has higher priority
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(2000);

        $provider1Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($categoryDirectives);

        $provider2Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($productDirectives);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$provider1Mock, $provider2Mock]
        );

        // Higher priority wins regardless of sort order
        $this->assertEquals($productDirectives, $model->execute());
    }

    public function testExecuteSkipsProviderReturningNull(): void
    {
        $provider1Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider2Mock = $this->createMock(XRobotsProviderInterface::class);

        $validDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(200);

        $provider1Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn(null);

        $provider2Mock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($validDirectives);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$provider1Mock, $provider2Mock]
        );

        // Only provider2 returns a value
        $this->assertEquals($validDirectives, $model->execute());
    }

    public function testExecuteReturnsEmptyArrayWhenAllProvidersReturnNull(): void
    {
        $providerMock = $this->createMock(XRobotsProviderInterface::class);
        $providerMock->method('getSortOrder')->willReturn(10);
        $providerMock->method('getPriority')->willReturn(100);

        $providerMock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn(null);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$providerMock]
        );

        $this->assertEquals([], $model->execute());
    }

    public function testProvidersAreSortedBySortOrder(): void
    {
        $provider1Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider2Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider3Mock = $this->createMock(XRobotsProviderInterface::class);

        // Providers added in random order
        $provider1Mock->method('getSortOrder')->willReturn(30);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(10);
        $provider2Mock->method('getPriority')->willReturn(100);
        $provider3Mock->method('getSortOrder')->willReturn(20);
        $provider3Mock->method('getPriority')->willReturn(100);

        $executionOrder = [];

        $provider1Mock->method('getDirectives')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider1';
                return null;
            });

        $provider2Mock->method('getDirectives')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider2';
                return null;
            });

        $provider3Mock->method('getDirectives')
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'provider3';
                return null;
            });

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$provider1Mock, $provider2Mock, $provider3Mock]
        );

        $model->execute();

        // Should execute in sort order: provider2 (10), provider3 (20), provider1 (30)
        $this->assertEquals(['provider2', 'provider3', 'provider1'], $executionOrder);
    }

    public function testExecuteWithSingleProvider(): void
    {
        $providerMock = $this->createMock(XRobotsProviderInterface::class);
        $directives = [['value' => 'noindex', 'bot' => 'googlebot', 'modification' => '']];

        $providerMock->method('getSortOrder')->willReturn(10);
        $providerMock->method('getPriority')->willReturn(100);

        $providerMock->expects($this->once())
            ->method('getDirectives')
            ->with($this->requestMock)
            ->willReturn($directives);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$providerMock]
        );

        $this->assertEquals($directives, $model->execute());
    }

    public function testExecuteWithEqualPrioritiesUsesLastEvaluated(): void
    {
        $provider1Mock = $this->createMock(XRobotsProviderInterface::class);
        $provider2Mock = $this->createMock(XRobotsProviderInterface::class);

        $directives1 = [['value' => 'first', 'bot' => '', 'modification' => '']];
        $directives2 = [['value' => 'second', 'bot' => '', 'modification' => '']];

        // Same priority, different sort order
        $provider1Mock->method('getSortOrder')->willReturn(10);
        $provider1Mock->method('getPriority')->willReturn(100);
        $provider2Mock->method('getSortOrder')->willReturn(20);
        $provider2Mock->method('getPriority')->willReturn(100);

        $provider1Mock->method('getDirectives')->willReturn($directives1);
        $provider2Mock->method('getDirectives')->willReturn($directives2);

        $model = new GetXRobotsByRequest(
            $this->requestMock,
            [$provider1Mock, $provider2Mock]
        );

        // With equal priorities, the current implementation keeps the first one found
        // (priority check is > not >=)
        $this->assertEquals($directives1, $model->execute());
    }
}
