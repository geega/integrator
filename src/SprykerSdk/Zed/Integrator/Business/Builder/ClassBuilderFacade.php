<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\Builder;

use Generated\Shared\Transfer\ClassInformationTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \SprykerSdk\Zed\Integrator\Business\IntegratorBusinessFactory getFactory()
 */
class ClassBuilderFacade extends AbstractFacade
{
    /**
     * @param string $targetClassName
     * @param string $customOrganisation
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function resolveClass(string $targetClassName, string $customOrganisation = ''): ClassInformationTransfer
    {
        return $this->getFactory()
            ->createClassResolver()
            ->resolveClass($targetClassName, $customOrganisation);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     *
     * @return bool
     */
    public function storeClass(ClassInformationTransfer $classInformationTransfer): bool
    {
        return $this->getFactory()
            ->createClassFileWriter()
            ->storeClass($classInformationTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     *
     * @return string|null
     */
    public function printDiff(ClassInformationTransfer $classInformationTransfer): ?string
    {
        return $this->getFactory()
            ->createClassDiffPrinter()
            ->printDiff($classInformationTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $constantName
     * @param $value
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function setConstant(ClassInformationTransfer $classInformationTransfer, string $constantName, $value): ClassInformationTransfer
    {
        return $this->getFactory()
            ->createClassConstantModifier()
            ->setConstant($classInformationTransfer, $constantName, $value);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $targetMethodName
     * @param string $classNameToAdd
     * @param string|null $before
     * @param string|null $after
     * @param string|null $key
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function wireClassInstance(
        ClassInformationTransfer $classInformationTransfer,
        string $targetMethodName,
        string $classNameToAdd,
        string $before = '',
        string $after = '',
        ?string $key = null
    ): ClassInformationTransfer {
        return $this->getFactory()
            ->createClassInstanceClassModifier()
            ->wireClassInstance($classInformationTransfer, $targetMethodName, $classNameToAdd, $before, $after, $key);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $classNameToRemove
     * @param string $targetMethodName
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer|null
     */
    public function unwireClassInstance(
        ClassInformationTransfer $classInformationTransfer,
        string $classNameToRemove,
        string $targetMethodName
    ): ?ClassInformationTransfer {
        return $this->getFactory()
            ->createClassInstanceClassModifier()
            ->unwireClassInstance($classInformationTransfer, $classNameToRemove, $targetMethodName);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $targetMethodName
     * @param string $classNameToAdd
     * @param string $constantName
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function wireClassConstant(
        ClassInformationTransfer $classInformationTransfer,
        string $targetMethodName,
        string $classNameToAdd,
        string $constantName
    ): ClassInformationTransfer {
        return $this->getFactory()
            ->createClassListModifier()
            ->wireClassConstant($classInformationTransfer, $targetMethodName, $classNameToAdd, $constantName);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $classNameToRemove
     * @param string $targetMethodName
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer|null
     */
    public function unwireClassConstant(
        ClassInformationTransfer $classInformationTransfer,
        string $classNameToRemove,
        string $targetMethodName
    ): ?ClassInformationTransfer {
        return $this->getFactory()
            ->createClassListModifier()
            ->unwireClassConstant($classInformationTransfer, $classNameToRemove, $targetMethodName);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $methodName
     * @param bool|int|float|string|array|null $value
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function setMethodReturnValue(ClassInformationTransfer $classInformationTransfer, string $methodName, $value): ClassInformationTransfer
    {
        return $this->getFactory()
            ->createCommonClassModifier()
            ->setMethodReturnValue($classInformationTransfer, $methodName, $value);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $targetMethodName
     * @param string $key
     * @param string $classNameToAdd
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function wireGlueRelationship(
        ClassInformationTransfer $classInformationTransfer,
        string $targetMethodName,
        string $key,
        string $classNameToAdd
    ): ClassInformationTransfer {
        return $this->getFactory()
            ->createGlueRelationshipModifier()
            ->wireGlueRelationship($classInformationTransfer, $targetMethodName, $key, $classNameToAdd);
    }

    /**
     * @param \Generated\Shared\Transfer\ClassInformationTransfer $classInformationTransfer
     * @param string $targetMethodName
     * @param string $key
     * @param string $classNameToAdd
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer
     */
    public function unwireGlueRelationship(
        ClassInformationTransfer $classInformationTransfer,
        string $targetMethodName,
        string $key,
        string $classNameToAdd
    ): ClassInformationTransfer {
        return $this->getFactory()
            ->createGlueRelationshipModifier()
            ->unwireGlueRelationship($classInformationTransfer, $targetMethodName, $key, $classNameToAdd);
    }
}
