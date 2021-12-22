<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoApi\Api\GetBaseUrlInterface;
use Hryvinskyi\SeoApi\Api\CheckPatternInterface;
use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetRobotsByRequest implements GetRobotsByRequestInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var RobotsListInterface
     */
    private $robotsList;

    /**
     * @var CheckPatternInterface
     */
    private $checkPattern;

    /**
     * @var GetBaseUrlInterface
     */
    private $baseUrl;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     * @param RobotsListInterface $robotsList
     * @param CheckPatternInterface $checkPattern
     * @param GetBaseUrlInterface $baseUrl
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        RobotsListInterface $robotsList,
        CheckPatternInterface $checkPattern,
        GetBaseUrlInterface $baseUrl
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->robotsList = $robotsList;
        $this->checkPattern = $checkPattern;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request): ?string
    {
        if (($code = $this->config->getHttpsMetaRobots()) && $this->storeManager->getStore()->isCurrentlySecure()) {
            return $this->robotsList->getMetaRobotsByCode((int)$code);
        }

        $fullAction = $request->getFullActionName();
        $robots = $this->config->getMetaRobots();
        usort($robots, static function ($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        foreach ($robots as $robot) {
            if ($this->checkPattern->execute($fullAction, $robot['pattern'])
                || $this->checkPattern->execute($this->baseUrl->execute(), $robot['pattern'])
            ) {
                return $this->robotsList->getMetaRobotsByCode((int)$robot['option']);
            }
        }

        return null;
    }
}
