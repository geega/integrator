<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\Manifest;

use Generated\Shared\Transfer\ModuleTransfer;
use SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader;
use SprykerSdk\Zed\Integrator\IntegratorConfig;

class ManifestReader
{
    /**
     * @var \SprykerSdk\Zed\Integrator\IntegratorConfig
     */
    protected $config;

    /**
     * @var \SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader
     */
    protected $composerLockReader;

    /**
     * @param \SprykerSdk\Zed\Integrator\Business\Composer\ComposerLockReader $composerLockReader
     * @param \SprykerSdk\Zed\Integrator\IntegratorConfig $config
     */
    public function __construct(ComposerLockReader $composerLockReader, IntegratorConfig $config)
    {
        $this->config = $config;
        $this->composerLockReader = $composerLockReader;
    }

    /**
     * @param \Generated\Shared\Transfer\ModuleTransfer[] $moduleTransfers
     *
     * @return string[][][]
     */
    public function readManifests(array $moduleTransfers): array
    {
        $this->updateRepositoryFolder();
        $manifests = [];
        $moduleComposerData = $this->composerLockReader->getModuleVersions();
        foreach ($moduleTransfers as $moduleTransfer) {
            $moduleFullName = $moduleTransfer->getOrganization()->getName() . '.' . $moduleTransfer->getName();
            if (!isset($moduleComposerData[$moduleFullName])) {
                continue;
            }

            $filePath = $this->resolveManifestVersion($moduleTransfer, $moduleComposerData[$moduleFullName]);

            if (!$filePath) {
                continue;
            }

            $manifest = json_decode(file_get_contents($filePath), true);

            if ($manifest) {
                $manifests[$moduleFullName] = $manifest;
            }
        }

        return $manifests;
    }

    /**
     * @return void
     */
    protected function updateRepositoryFolder(): void
    {
        $recipesArchive = $this->config->getRecipiesDirectory() . 'archive.zip';
        if (!is_dir($this->config->getRecipiesDirectory())) {
            mkdir($this->config->getRecipiesDirectory(), 0700, true);
        }
        file_put_contents($recipesArchive, fopen($this->config->getRecipiesRepository(), 'r'));

        $zip = new \ZipArchive;
        $zip->open($recipesArchive);
        $zip->extractTo($this->config->getRecipiesDirectory());
        $zip->close();
    }

    /**
     * @param \Generated\Shared\Transfer\ModuleTransfer $moduleTransfer
     * @param string $moduleVersion
     *
     * @return string|string[]|null
     */
    protected function resolveManifestVersion(ModuleTransfer $moduleTransfer, string $moduleVersion)
    {
        $archiveDir = 'integrator-recipes-master/';
        $moduleRecipiesDir = sprintf('%s%s%s/', $this->config->getRecipiesDirectory(), $archiveDir, $moduleTransfer->getName());

        if (!is_dir($moduleRecipiesDir)) {
            return null;
        }

        $filePath = $moduleRecipiesDir . sprintf(
                '%s/installer-manifest.json',
                $moduleVersion
            );

        if (file_exists($filePath)) {
            return $filePath;
        }

        $nextSuitableVersion = $this->findNextSuitableVersion($moduleRecipiesDir, $moduleVersion);

        if (!$nextSuitableVersion) {
            return null;
        }

        return $moduleRecipiesDir . sprintf(
                '%s/installer-manifest.json',
                $nextSuitableVersion
            );
    }

    /**
     * @param string $moduleRecipesDir
     * @param string $moduleVersion
     *
     * @return string|null
     */
    protected function findNextSuitableVersion(string $moduleRecipesDir, string $moduleVersion): ?string
    {
        $versions = [];

        foreach ($this->getValidModuleVersions($moduleRecipesDir) as $version) {
            if (version_compare($version, $moduleVersion, 'gt')) {
                continue;
            }

            $versions[] = $version;
        }

        if (!$versions) {
            return null;
        }

        $versions = $this->sortArray($versions);

        return end($versions);
    }

    /**
     * @param string $moduleRecipesDir
     *
     * @return string[]
     */
    protected function getValidModuleVersions(string $moduleRecipesDir): array
    {
        $validModuleVersions = [];
        $moduleVersionDirectories = scandir($moduleRecipesDir);
        $moduleVersionDirectories = array_diff($moduleVersionDirectories, ['.', '..']);

        foreach ($moduleVersionDirectories as $moduleVersionDirectory) {
            if (!is_dir($moduleRecipesDir . $moduleVersionDirectory)) {
                continue;
            }

            $validModuleVersions[] = $moduleVersionDirectory;
        }

        return $validModuleVersions;
    }

    /**
     * @param string[] $versions
     *
     * @return string[]
     */
    protected function sortArray(array $versions): array
    {
        if (count($versions) === 0) {
            return [];
        }

        $pivotArray = array_splice(
            $versions,
            (int)floor((count($versions) - 1) / 2),
            1
        );

        $smaller = [];
        $greater = [];

        foreach ($versions as $version) {
            if (version_compare($version, $pivotArray[0], 'gt')) {
                $greater[] = $version;
            } else {
                $smaller[] = $version;
            }
        }

        return array_merge(
            $this->sortArray($smaller),
            $pivotArray,
            $this->sortArray($greater)
        );
    }
}
