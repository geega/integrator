<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\ManifestStrategy;

use SprykerSdk\Zed\Integrator\Dependency\Console\InputOutputInterface;

interface ManifestStrategyInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string[] $manifest
     * @param string $moduleName
     * @param \SprykerSdk\Zed\Integrator\Dependency\Console\InputOutputInterface $inputOutput
     * @param bool $isDry
     *
     * @return bool
     */
    public function apply(array $manifest, string $moduleName, InputOutputInterface $inputOutput, bool $isDry): bool;
}
