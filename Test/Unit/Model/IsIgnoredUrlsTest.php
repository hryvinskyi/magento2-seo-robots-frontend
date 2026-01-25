<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Unit\Model;

use Hryvinskyi\SeoRobotsFrontend\Model\IgnoredUrlsList;
use Hryvinskyi\SeoRobotsFrontend\Model\IsIgnoredUrls;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IsIgnoredUrlsTest extends TestCase
{
    private IsIgnoredUrls $model;
    private UrlInterface|MockObject $urlMock;
    private IgnoredUrlsList|MockObject $ignoredUrlsListMock;

    protected function setUp(): void
    {
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->ignoredUrlsListMock = $this->createMock(IgnoredUrlsList::class);

        $this->model = new IsIgnoredUrls($this->urlMock, $this->ignoredUrlsListMock);
    }

    public function testExecuteReturnsTrueForCheckoutUrl(): void
    {
        $this->urlMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn('https://example.com/checkout/cart');

        $this->ignoredUrlsListMock->expects($this->once())
            ->method('getList')
            ->willReturn(['checkout/', 'onestepcheckout']);

        $this->assertTrue($this->model->execute());
    }

    public function testExecuteReturnsTrueForOnestepCheckoutUrl(): void
    {
        $this->urlMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn('https://example.com/onestepcheckout/index');

        $this->ignoredUrlsListMock->expects($this->once())
            ->method('getList')
            ->willReturn(['checkout/', 'onestepcheckout']);

        $this->assertTrue($this->model->execute());
    }

    public function testExecuteReturnsFalseForNonIgnoredUrl(): void
    {
        $this->urlMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn('https://example.com/catalog/product/view/id/123');

        $this->ignoredUrlsListMock->expects($this->once())
            ->method('getList')
            ->willReturn(['checkout/', 'onestepcheckout']);

        $this->assertFalse($this->model->execute());
    }

    public function testExecuteReturnsFalseWhenIgnoredListIsEmpty(): void
    {
        $this->urlMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn('https://example.com/checkout/cart');

        $this->ignoredUrlsListMock->expects($this->once())
            ->method('getList')
            ->willReturn([]);

        $this->assertFalse($this->model->execute());
    }

    /**
     * @dataProvider ignoredUrlsDataProvider
     *
     * @param string $currentUrl
     * @param array<string> $ignoredUrls
     * @param bool $expectedResult
     */
    public function testExecuteWithVariousUrls(
        string $currentUrl,
        array $ignoredUrls,
        bool $expectedResult
    ): void {
        $this->urlMock->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn($currentUrl);

        $this->ignoredUrlsListMock->expects($this->once())
            ->method('getList')
            ->willReturn($ignoredUrls);

        $this->assertEquals($expectedResult, $this->model->execute());
    }

    /**
     * @return array<string, array{currentUrl: string, ignoredUrls: array<string>, expectedResult: bool}>
     */
    public static function ignoredUrlsDataProvider(): array
    {
        return [
            'checkout cart page' => [
                'currentUrl' => 'https://example.com/checkout/cart',
                'ignoredUrls' => ['checkout/'],
                'expectedResult' => true,
            ],
            'checkout success page' => [
                'currentUrl' => 'https://example.com/checkout/onepage/success',
                'ignoredUrls' => ['checkout/'],
                'expectedResult' => true,
            ],
            'category page' => [
                'currentUrl' => 'https://example.com/women/tops.html',
                'ignoredUrls' => ['checkout/', 'onestepcheckout'],
                'expectedResult' => false,
            ],
            'product page' => [
                'currentUrl' => 'https://example.com/product-name.html',
                'ignoredUrls' => ['checkout/', 'onestepcheckout'],
                'expectedResult' => false,
            ],
            'homepage' => [
                'currentUrl' => 'https://example.com/',
                'ignoredUrls' => ['checkout/', 'onestepcheckout'],
                'expectedResult' => false,
            ],
            'custom ignored path' => [
                'currentUrl' => 'https://example.com/my-account/orders',
                'ignoredUrls' => ['my-account/'],
                'expectedResult' => true,
            ],
            'partial match in middle of url' => [
                'currentUrl' => 'https://example.com/store/checkout/payment',
                'ignoredUrls' => ['checkout/'],
                'expectedResult' => true,
            ],
        ];
    }
}
