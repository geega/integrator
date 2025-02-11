<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerSdk\Zed\Integrator\Business\Manifest;

use Generated\Shared\Transfer\ModuleTransfer;
use SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader;

class ManifestWriter
{
    /**
     * @var \SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader
     */
    protected $composerLockReader;

    /**
     * @param \SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader $composerLockReader
     */
    public function __construct(ComposerLockReader $composerLockReader)
    {
        $this->composerLockReader = $composerLockReader;
    }

    /**
     * @param \Generated\Shared\Transfer\ModuleTransfer[] $moduleTransfers
     * @param array $manifests
     *
     * @return bool
     */
    public function storeManifest(array $moduleTransfers, array $manifests): bool
    {
        $moduleComposerData = $this->composerLockReader->getModuleVersions();
        $success = true;
        foreach ($manifests as $moduleKey => $manifest) {
            $moduleName = explode('.', $moduleKey)[1];
            if (!isset($moduleTransfers[$moduleName], $moduleComposerData[$moduleKey])) {
                continue;
            }
            $moduleTransfer = $moduleTransfers[$moduleName];

            $lockFileDir = $this->getManifestFilePath($moduleTransfer, $moduleComposerData[$moduleKey]);
            $json = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL;
            if (!is_dir($lockFileDir)) {
                mkdir($lockFileDir, 0700, true);
            }
            if (!file_put_contents($lockFileDir . 'installer-manifest.json', $json) === false) {
                $success = false;
            }
        }

        return $success;
    }


    /**
     * @param \Generated\Shared\Transfer\ModuleTransfer $moduleTransfer
     * @param string $moduleVersion
     *
     * @return string
     */
    protected function getManifestFilePath(ModuleTransfer $moduleTransfer, string $moduleVersion): string
    {
        return APPLICATION_ROOT_DIR . sprintf(
                '/vendor/spryker-sdk/integrator/data/recipies/integrator-recipes-master/%s/%s/',
                $moduleTransfer->getName(),
                $moduleVersion
            );
    }
}
