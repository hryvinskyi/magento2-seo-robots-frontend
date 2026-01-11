<?php
/**
 * Copyright (c) 2021-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\UrlInterface;

class IsIgnoredUrls implements IsIgnoredUrlsInterface
{
    public function __construct(
        private readonly UrlInterface $url,
        private readonly IgnoredUrlsList $ignoredUrlsList
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $currentUrl = $this->url->getCurrentUrl();

        foreach ($this->ignoredUrlsList->getList() as $urlPart) {
            if (str_contains($currentUrl, $urlPart)) {
                return true;
            }
        }

        return false;
    }
}
