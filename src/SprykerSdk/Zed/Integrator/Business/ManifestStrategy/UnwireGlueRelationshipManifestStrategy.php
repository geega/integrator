<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\ManifestStrategy;

use SprykerSdk\Zed\Integrator\Dependency\Console\InputOutputInterface;
use SprykerSdk\Zed\Integrator\IntegratorConfig;

class UnwireGlueRelationshipManifestStrategy extends AbstractManifestStrategy
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'unwire-glue-relationship';
    }

    /**
     * @param string[] $manifest
     * @param string $moduleName
     * @param \SprykerSdk\Zed\Integrator\Dependency\Console\InputOutputInterface $inputOutput
     * @param bool $isDry
     *
     * @return bool
     */
    public function apply(array $manifest, string $moduleName, InputOutputInterface $inputOutput, bool $isDry): bool
    {
        $targetClassName = '\Spryker\Glue\GlueApplication\GlueApplicationDependencyProvider';
        $targetMethodName = 'getResourceRelationshipPlugins';

        $applied = false;
        foreach ($this->config->getProjectNamespaces() as $namespace) {
            $classInformationTransfer = $this->getClassBuilderFacade()->resolveClass($targetClassName, $namespace);
            if (!$classInformationTransfer) {
                continue;
            }

            $targetClass = $manifest[IntegratorConfig::MANIFEST_KEY_SOURCE];
            $targetClassKey = null;
            if (is_array($targetClass)) {
                foreach ($targetClass as $key => $class) {
                    $targetClass = $class;
                    $targetClassKey = $key;

                    break;
                }
            }
            if (!defined($targetClassKey)) {
                continue;
            }

            $classInformationTransfer = $this->getClassBuilderFacade()->unwireGlueRelationship(
                $classInformationTransfer,
                $targetMethodName,
                $targetClassKey,
                $targetClass
            );

            if ($isDry) {
                $applied = $inputOutput->writeln($this->getClassBuilderFacade()->printDiff($classInformationTransfer));
            } else {
                $applied = $this->getClassBuilderFacade()->storeClass($classInformationTransfer);
            }

            $inputOutput->writeln(sprintf(
                'GLUE relationship %s was removed from %s::%s',
                $targetClassKey,
                $classInformationTransfer->getClassName(),
                $targetMethodName
            ), InputOutputInterface::DEBUG);
        }

        return $applied;
    }
}
