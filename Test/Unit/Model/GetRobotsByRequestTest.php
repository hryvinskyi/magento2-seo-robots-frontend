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
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Hryvinskyi\SeoRobotsFrontend\Model\GetRobotsByRequest;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetRobotsByRequestTest extends TestCase
{
    private GetRobotsByRequest $model;
    private ConfigInterface|MockObject $configMock;
    private RobotsListInterface|MockObject $robotsListMock;
    private CheckPatternInterface|MockObject $checkPatternMock;
    private GetBaseUrlInterface|MockObject $baseUrlMock;
    private SerializerInterface|MockObject $serializerMock;
    private HttpRequestInterface|MockObject $requestMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->robotsListMock = $this->createMock(RobotsListInterface::class);
        $this->checkPatternMock = $this->createMock(CheckPatternInterface::class);
        $this->baseUrlMock = $this->createMock(GetBaseUrlInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->requestMock = $this->getMockBuilder(HttpRequestInterface::class)
            ->addMethods(['getFullActionName'])
            ->getMockForAbstractClass();

        $this->model = new GetRobotsByRequest(
            $this->configMock,
            $this->robotsListMock,
            $this->checkPatternMock,
            $this->baseUrlMock,
            $this->serializerMock
        );
    }

    public function testExecuteReturnsNullWhenNoRobotsConfigured(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([]);

        $this->assertNull($this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsNullWhenNoPatternMatches(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'checkout_*',
                    'priority' => 10,
                    'meta_directives' => [['value' => 'noindex', 'bot' => '', 'modification' => '']],
                ],
            ]);

        $this->baseUrlMock->expects($this->once())
            ->method('execute')
            ->willReturn('https://example.com/product.html');

        $this->checkPatternMock->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(false);

        $this->assertNull($this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsRobotsWhenActionPatternMatches(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $directives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'meta_directives' => $directives,
                ],
            ]);

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->with('catalog_product_view', 'catalog_product_*')
            ->willReturn(true);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($directives)
            ->willReturn('NOINDEX');

        $this->assertEquals('NOINDEX', $this->model->execute($this->requestMock));
    }

    public function testExecuteReturnsRobotsWhenUrlPatternMatches(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $directives = [['value' => 'nofollow', 'bot' => '', 'modification' => '']];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => '*product.html',
                    'priority' => 10,
                    'meta_directives' => $directives,
                ],
            ]);

        $this->baseUrlMock->expects($this->once())
            ->method('execute')
            ->willReturn('https://example.com/product.html');

        $this->checkPatternMock->expects($this->exactly(2))
            ->method('execute')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($directives)
            ->willReturn('NOFOLLOW');

        $this->assertEquals('NOFOLLOW', $this->model->execute($this->requestMock));
    }

    public function testExecuteSortsRulesByPriorityDescending(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $lowPriorityDirectives = [['value' => 'index', 'bot' => '', 'modification' => '']];
        $highPriorityDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'catalog_*',
                    'priority' => 5,
                    'meta_directives' => $lowPriorityDirectives,
                ],
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'meta_directives' => $highPriorityDirectives,
                ],
            ]);

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->with('catalog_product_view', 'catalog_product_*')
            ->willReturn(true);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($highPriorityDirectives)
            ->willReturn('NOINDEX');

        $this->assertEquals('NOINDEX', $this->model->execute($this->requestMock));
    }

    public function testExecuteHandlesSerializedStringDirectives(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $serializedDirectives = '["noindex","nofollow"]';
        $unserializedDirectives = ['noindex', 'nofollow'];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'meta_directives' => $serializedDirectives,
                ],
            ]);

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedDirectives)
            ->willReturn($unserializedDirectives);

        $this->robotsListMock->expects($this->once())
            ->method('buildMetaRobotsFromDirectives')
            ->with($unserializedDirectives)
            ->willReturn('NOINDEX, NOFOLLOW');

        $this->assertEquals('NOINDEX, NOFOLLOW', $this->model->execute($this->requestMock));
    }

    public function testExecuteHandlesSimpleArrayDirectives(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $simpleDirectives = ['noindex', 'nofollow'];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 10,
                    'meta_directives' => $simpleDirectives,
                ],
            ]);

        $this->checkPatternMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->robotsListMock->expects($this->once())
            ->method('buildMetaRobotsFromDirectives')
            ->with($simpleDirectives)
            ->willReturn('NOINDEX, NOFOLLOW');

        $this->assertEquals('NOINDEX, NOFOLLOW', $this->model->execute($this->requestMock));
    }

    public function testExecuteSkipsRulesWithEmptyDirectives(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $validDirectives = [['value' => 'noindex', 'bot' => '', 'modification' => '']];

        $this->configMock->expects($this->once())
            ->method('getMetaRobots')
            ->willReturn([
                [
                    'pattern' => 'catalog_product_*',
                    'priority' => 20,
                    'meta_directives' => [],
                ],
                [
                    'pattern' => 'catalog_*',
                    'priority' => 10,
                    'meta_directives' => $validDirectives,
                ],
            ]);

        $this->baseUrlMock->method('execute')
            ->willReturn('https://example.com/product.html');

        $this->checkPatternMock->method('execute')
            ->willReturn(true);

        $this->robotsListMock->expects($this->once())
            ->method('buildFromStructuredDirectives')
            ->with($validDirectives)
            ->willReturn('NOINDEX');

        $this->assertEquals('NOINDEX', $this->model->execute($this->requestMock));
    }
}
