<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\Builder\ClassModifier;

use Generated\Shared\Transfer\ClassInformationTransfer;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use SprykerSdk\Zed\Integrator\Business\Builder\Checker\ClassMethodChecker;
use SprykerSdk\Zed\Integrator\Business\Builder\Finder\ClassNodeFinder;
use SprykerSdk\Zed\Integrator\Business\Builder\Visitor\AddClassToClassListVisitor;
use SprykerSdk\Zed\Integrator\Business\Builder\Visitor\AddUseVisitor;
use SprykerSdk\Zed\Integrator\Business\Builder\Visitor\RemoveClassFromClassListVisitor;
use SprykerSdk\Zed\Integrator\Business\Helper\ClassHelper;

class ClassListModifier
{
    /**
     * @var \PhpParser\NodeTraverser
     */
    protected $nodeTraverser;

    /**
     * @var \SprykerSdk\Zed\Integrator\Business\Builder\ClassModifier\CommonClassModifier
     */
    protected $commonClassModifier;

    /**
     * @var \SprykerSdk\Zed\Integrator\Business\Builder\Finder\ClassNodeFinder
     */
    protected $classNodeFinder;

    /**
     * @param \PhpParser\NodeTraverser $nodeTraverser
     * @param \SprykerSdk\Zed\Integrator\Business\Builder\ClassModifier\CommonClassModifier $commonClassModifier
     * @param \SprykerSdk\Zed\Integrator\Business\Builder\Finder\ClassNodeFinder $classNodeFinder
     */
    public function __construct(
        NodeTraverser $nodeTraverser,
        CommonClassModifier $commonClassModifier,
        ClassNodeFinder $classNodeFinder
    ) {
        $this->nodeTraverser = $nodeTraverser;
        $this->commonClassModifier = $commonClassModifier;
        $this->classNodeFinder = $classNodeFinder;
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
        $methodNode = $this->classNodeFinder->findMethodNode($classInformationTransfer, $targetMethodName);
        if (!$methodNode) {
            $classInformationTransfer = $this->commonClassModifier->overrideMethodFromParent($classInformationTransfer, $targetMethodName);
            $methodNode = $this->classNodeFinder->findMethodNode($classInformationTransfer, $targetMethodName);
        }

        $classMethodChecker = new ClassMethodChecker();
        if ($classMethodChecker->isMethodReturnArray($methodNode)) {
            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor(new AddUseVisitor($classNameToAdd));
            $nodeTraverser->addVisitor(
                new AddClassToClassListVisitor(
                    $targetMethodName,
                    $classNameToAdd,
                    $constantName
                )
            );

            $classInformationTransfer->setClassTokenTree($nodeTraverser->traverse($classInformationTransfer->getClassTokenTree()));

            return $classInformationTransfer;
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new AddUseVisitor($classNameToAdd));
        $classInformationTransfer->setClassTokenTree($nodeTraverser->traverse($classInformationTransfer->getClassTokenTree()));

        $classHelper = new ClassHelper();
        $methodBody = [new Return_((new BuilderFactory())->classConstFetch($classHelper->getShortClassName($classNameToAdd), $constantName))];

        $this->commonClassModifier->replaceMethodBody($classInformationTransfer, $targetMethodName, $methodBody);

        return $classInformationTransfer;
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
        $methodNode = $this->classNodeFinder->findMethodNode($classInformationTransfer, $targetMethodName);
        if (!$methodNode) {
            return null;
        }

        if (!(new ClassMethodChecker())->isMethodReturnArray($methodNode)) {
            return $this->commonClassModifier->removeClassMethod($classInformationTransfer, $targetMethodName);
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(
            new RemoveClassFromClassListVisitor(
                $targetMethodName,
                $classNameToRemove
            )
        );

        $classInformationTransfer->setClassTokenTree($nodeTraverser->traverse($classInformationTransfer->getClassTokenTree()));

        return $classInformationTransfer;
    }
}
