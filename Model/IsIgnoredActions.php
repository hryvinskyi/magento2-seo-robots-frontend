<?php
/**
 * Copyright (c) 2021-2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Model;

use Magento\Framework\App\HttpRequestInterface;

class IsIgnoredActions implements IsIgnoredActionsInterface
{
    public function __construct(private readonly IgnoredActionsList $ignoredActionsList)
    {
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
