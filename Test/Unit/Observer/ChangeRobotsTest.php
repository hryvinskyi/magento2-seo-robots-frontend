<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Observer;

use Hryvinskyi\SeoRobotsFrontend\Model\ApplyRobotsInterface;
use Hryvinskyi\SeoRobotsFrontend\Observer\ChangeRobots;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeRobotsTest extends TestCase
{
    private ApplyRobotsInterface|MockObject $applyRobotsMock;
    private PageConfig|MockObject $pageConfigMock;
    private RequestInterface|MockObject $requestMock;
    private Observer|MockObject $observerMock;

    protected function setUp(): void
    {
        $this->applyRobotsMock = $this->createMock(ApplyRobotsInterface::class);
        $this->pageConfigMock = $this->createMock(PageConfig::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->observerMock = $this->createMock(Observer::class);
    }

    public function testExecuteCallsApplyRobotsWhenRequestIsHttp(): void
    {
        $observer = new ChangeRobots(
            $this->applyRobotsMock,
            $this->pageConfigMock,
            $this->requestMock
        );

        $this->applyRobotsMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->requestMock, $this->pageConfigMock);

        $observer->execute($this->observerMock);
    }

    public function testExecuteDoesNotCallApplyRobotsWhenRequestIsNotHttp(): void
    {
        $nonHttpRequestMock = $this->createMock(RequestInterface::class);

        $observer = new ChangeRobots(
            $this->applyRobotsMock,
            $this->pageConfigMock,
            $nonHttpRequestMock
        );

        $this->applyRobotsMock->expects($this->never())
            ->method('execute');

        $observer->execute($this->observerMock);
    }
}
