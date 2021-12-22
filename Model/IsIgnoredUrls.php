<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\UrlInterface;

class IsIgnoredUrls implements IsIgnoredUrlsInterface
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var IgnoredUrlsList
     */
    private $ignoredUrlsList;

    /**
     * @param UrlInterface $url
     * @param IgnoredUrlsList $ignoredUrlsList
     */
    public function __construct(UrlInterface $url, IgnoredUrlsList $ignoredUrlsList)
    {
        $this->url = $url;
        $this->ignoredUrlsList = $ignoredUrlsList;
    }

    /**
     * @inheritDoc
     */
    public function execute(): bool
    {
        $currentUrl = $this->url->getCurrentUrl();

        foreach ($this->ignoredUrlsList->getList() as $urlPart) {
            if (strpos($currentUrl, $urlPart) !== false) {
                return true;
            }
        }

        return false;
    }
}
