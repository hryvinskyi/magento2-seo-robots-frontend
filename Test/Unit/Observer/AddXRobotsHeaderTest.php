<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Observer;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\GetXRobotsByRequestInterface;
use Hryvinskyi\SeoRobotsFrontend\Observer\AddXRobotsHeader;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Page\Config as PageConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddXRobotsHeaderTest extends TestCase
{
    private AddXRobotsHeader $observer;
    private ConfigInterface|MockObject $configMock;
    private PageConfig|MockObject $pageConfigMock;
    private LoggerInterface|MockObject $loggerMock;
    private GetXRobotsByRequestInterface|MockObject $getXRobotsByRequestMock;
    private RobotsListInterface|MockObject $robotsListMock;
    private Observer|MockObject $observerMock;
    private Event|MockObject $eventMock;
    private HttpResponse|MockObject $responseMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->pageConfigMock = $this->createMock(PageConfig::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->getXRobotsByRequestMock = $this->createMock(GetXRobotsByRequestInterface::class);
        $this->robotsListMock = $this->createMock(RobotsListInterface::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createMock(Event::class);
        $this->responseMock = $this->createMock(HttpResponse::class);

        $this->observerMock->method('getEvent')
            ->willReturn($this->eventMock);

        $this->observer = new AddXRobotsHeader(
            $this->configMock,
            $this->pageConfigMock,
            $this->loggerMock,
            $this->getXRobotsByRequestMock,
            $this->robotsListMock
        );
    }

    public function testExecuteDoesNothingWhenXHeaderIsDisabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(false);

        $this->responseMock->expects($this->never())
            ->method('setHeader');

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteDoesNothingWhenResponseIsNotHttp(): void
    {
        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn(null);

        $this->getXRobotsByRequestMock->expects($this->never())
            ->method('execute');

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteDoesNothingWhenResponseIsRedirect(): void
    {
        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('isRedirect')
            ->willReturn(true);

        $this->responseMock->expects($this->never())
            ->method('setHeader');

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteSetsXRobotsHeaderFromDirectives(): void
    {
        $xrobotsDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('isRedirect')
            ->willReturn(false);

        $this->getXRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn($xrobotsDirectives);

        $this->robotsListMock->expects($this->once())
            ->method('buildXRobotsFromStructuredDirectives')
            ->with($xrobotsDirectives)
            ->willReturn('noindex');

        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('X-Robots-Tag', 'noindex', true);

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteFallsBackToPageConfigRobots(): void
    {
        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('isRedirect')
            ->willReturn(false);

        $this->getXRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->once())
            ->method('getRobots')
            ->willReturn('NOINDEX, NOFOLLOW');

        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('X-Robots-Tag', 'NOINDEX, NOFOLLOW', true);

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteDoesNotSetHeaderWhenRobotsIsEmpty(): void
    {
        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('isRedirect')
            ->willReturn(false);

        $this->getXRobotsByRequestMock->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->pageConfigMock->expects($this->once())
            ->method('getRobots')
            ->willReturn('');

        $this->responseMock->expects($this->never())
            ->method('setHeader');

        $this->observer->execute($this->observerMock);
    }

    public function testExecuteLogsExceptionAndContinues(): void
    {
        $exception = new \Exception('Test exception');

        $this->configMock->expects($this->once())
            ->method('isRobotsXheaderEnabled')
            ->willReturn(true);

        $this->eventMock->expects($this->once())
            ->method('getData')
            ->with('response')
            ->willReturn($this->responseMock);

        $this->responseMock->expects($this->once())
            ->method('isRedirect')
            ->willThrowException($exception);

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'Error adding X-Robots-Tag header: Test exception',
                ['exception' => $exception]
            );

        $this->observer->execute($this->observerMock);
    }
}
