<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Hryvinskyi\SeoRobotsApi\Api\ConfigInterface;
use Hryvinskyi\SeoRobotsApi\Api\RobotsListInterface;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\View\Page\Config;

class ApplyRobots implements ApplyRobotsInterface
{
    /**
     * @var IsIgnoredActionsInterface
     */
    private $ignoredActions;

    /**
     * @var IsIgnoredUrlsInterface
     */
    private $isIgnoredUrls;

    /**
     * @var GetRobotsByRequestInterface
     */
    private $getRobotsByRequest;

    /**
     * @var RobotsListInterface
     */
    private $robotsList;
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var RobotsProviderInterface[]
     */
    private $robotsProviders;

    /**
     * @param IsIgnoredActionsInterface $ignoredActions
     * @param IsIgnoredUrlsInterface $isIgnoredUrls
     * @param GetRobotsByRequestInterface $getRobotsByRequest
     * @param RobotsListInterface $robotsList
     * @param ConfigInterface $config
     * @param array $robotsProviders
     */
    public function __construct(
        IsIgnoredActionsInterface $ignoredActions,
        IsIgnoredUrlsInterface $isIgnoredUrls,
        GetRobotsByRequestInterface $getRobotsByRequest,
        RobotsListInterface $robotsList,
        ConfigInterface $config,
        array $robotsProviders = []
    ) {
        $this->ignoredActions = $ignoredActions;
        $this->isIgnoredUrls = $isIgnoredUrls;
        $this->getRobotsByRequest = $getRobotsByRequest;
        $this->robotsList = $robotsList;
        $this->config = $config;
        $this->robotsProviders = $this->sortProviders($robotsProviders);
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request, Config $pageConfig): void
    {
        if ($this->config->isEnabled() === false || $this->isIgnoredUrls->execute() === true
            || $this->ignoredActions->execute($request) === true
        ) {
            return;
        }

        if ($robots = $this->getRobotsByRequest->execute($request)) {
            $pageConfig->setRobots($robots);
        }

        if ($this->config->isNoindexNofollowForNoRouteIndex() === true && $request->getControllerName() === 'noroute') {
            $pageConfig->setRobots($this->robotsList->getMetaRobotsByCode(RobotsListInterface::NOINDEX_NOFOLLOW));
        }

        if ($request->getParam('p') && (int)$request->getParam('p') > 1 && $this->config->isPaginatedRobots() === true) {
            $pageConfig->setRobots($this->robotsList->getMetaRobotsByCode($this->config->getPaginatedMetaRobots()));
        }

        // Apply custom robots from providers
        foreach ($this->robotsProviders as $provider) {
            if ($providerRobots = $provider->getRobots($request)) {
                $pageConfig->setRobots($providerRobots);
            }
        }
    }

    /**
     * Sort providers by sort order
     *
     * @param array $providers
     * @return array
     */
    private function sortProviders(array $providers): array
    {
        usort($providers, function (RobotsProviderInterface $a, RobotsProviderInterface $b) {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $providers;
    }
}
