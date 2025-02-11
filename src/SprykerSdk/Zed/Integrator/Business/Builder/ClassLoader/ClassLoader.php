<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerSdk\Zed\Integrator\Business\Builder\ClassLoader;

use Generated\Shared\Transfer\ClassInformationTransfer;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use ReflectionClass;

class ClassLoader
{
    /**
     * @var \PhpParser\Parser
     */
    protected $parser;

    /**
     * @var \PhpParser\Lexer
     */
    protected $lexer;

    /**
     * @param \PhpParser\Parser $parser
     * @param \PhpParser\Lexer $lexer
     */
    public function __construct(Parser $parser, Lexer $lexer)
    {
        $this->parser = $parser;
        $this->lexer = $lexer;
    }

    /**
     * @param string $className
     *
     * @return \Generated\Shared\Transfer\ClassInformationTransfer|null
     */
    public function loadClass(string $className): ?ClassInformationTransfer
    {
        $className = ltrim($className, '\\');

        $classInformationTransfer = (new ClassInformationTransfer())
            ->setClassName($className)
            ->setFullyQualifiedClassName('\\' . $className);

        $reflectionClass = (new ReflectionClass($className));

        $originalAst = $this->parser->parse(file_get_contents($reflectionClass->getFileName()));

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new CloningVisitor());
        $nodeTraverser->addVisitor(new NameResolver());
        $ast = $nodeTraverser->traverse($originalAst);

        $classInformationTransfer->setClassTokenTree($ast)
            ->setOriginalClassTokenTree($originalAst)
            ->setTokens($this->lexer->getTokens())
            ->setFilePath($reflectionClass->getFileName());

        if ($reflectionClass->getParentClass()) {
            $classInformationTransfer->setParent(
                $this->loadClass($reflectionClass->getParentClass()->getName())
            );
        }

        return $classInformationTransfer;
    }
}
