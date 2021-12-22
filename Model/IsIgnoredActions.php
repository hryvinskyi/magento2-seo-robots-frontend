<?php
/**
 * Copyright (c) 2021. All rights reserved.
 * @author: Volodymyr Hryvinskyi <mailto:volodymyr@hryvinskyi.com>
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;

class IsIgnoredActions implements IsIgnoredActionsInterface
{
    /**
     * @var IgnoredActionsList
     */
    private $ignoredActionsList;

    /**
     * @param IgnoredActionsList $ignoredActionsList
     */
    public function __construct(IgnoredActionsList $ignoredActionsList)
    {
        $this->ignoredActionsList = $ignoredActionsList;
    }

    /**
     * @inheritDoc
     */
    public function execute(HttpRequestInterface $request): bool
    {
        if ($request->isAjax()) {
            return true;
        }

        if (in_array($this->getFullActionCode($request), $this->ignoredActionsList->getList(), true)) {
            return true;
        }

        return false;
    }

    /**
     * @param HttpRequestInterface $request
     *
     * @return string
     */
    public function getFullActionCode(HttpRequestInterface $request): string
    {
        return strtolower(
            $request->getModuleName() . '_' . $request->getControllerName() . '_' . $request->getActionName()
        );
    }
}
