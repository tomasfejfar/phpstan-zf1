<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

use LogicException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;

class TableExtension implements DynamicMethodReturnTypeExtension
{
    const METHOD_CREATE_ROW = 'createRow';
    const METHOD_FETCH_ALL = 'fetchAll';
    const METHOD_FETCH_ROW = 'fetchRow';

    private RowClassReflector $rowClassReflector;

    public function __construct()
    {
        $this->rowClassReflector = new RowClassReflector();
    }

    public function getClass(): string
    {
        return 'Zend_Db_Table_Abstract';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), $this->supportedMethods());
    }

    /** @return string[] */
    protected function supportedMethods(): array
    {
        return [
            self::METHOD_FETCH_ROW,
            self::METHOD_FETCH_ALL,
            self::METHOD_CREATE_ROW,
        ];
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        if ($methodReflection->getName() === self::METHOD_FETCH_ROW) {
            return $this->handleFetchRow($scope, $methodCall, $methodReflection);
        } elseif ($methodReflection->getName() === self::METHOD_FETCH_ALL) {
            return $this->handleFetchAll($scope, $methodCall, $methodReflection);
        } elseif ($methodReflection->getName() === self::METHOD_CREATE_ROW) {
            return $this->handleCreateRow($scope, $methodCall, $methodReflection);
        }
        throw new LogicException(sprintf('Unsupported method "%s"', $methodReflection->getName()));
    }

    private function getOriginalReturnType(
        Scope $scope,
        MethodCall $methodCall,
        MethodReflection $methodReflection
    ): Type {
        $variant = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $methodCall->args,
            $methodReflection->getVariants()
        );
        return $variant->getReturnType();
    }

    private function handleFetchRow(
        Scope $scope,
        MethodCall $methodCall,
        MethodReflection $methodReflection
    ): Type {
        $originalReturnType = $this->getOriginalReturnType($scope, $methodCall, $methodReflection);

        $dbTableClass = $scope->getType($methodCall->var);

        if (!$dbTableClass instanceof TypeWithClassName) {
            return $originalReturnType;
        }

        $dbTableRowClass = $this->getDbTableRowClass($dbTableClass);

        if ($dbTableRowClass === null) {
            return $originalReturnType;
        }

        return TypeCombinator::union($dbTableRowClass, new NullType());
    }

    private function handleFetchAll(
        Scope $scope,
        MethodCall $methodCall,
        MethodReflection $methodReflection
    ): Type {
        $originalReturnType = $this->getOriginalReturnType($scope, $methodCall, $methodReflection);

        $dbTableClass = $scope->getType($methodCall->var);

        if (!$dbTableClass instanceof TypeWithClassName) {
            return $originalReturnType;
        }

        $dbTableRowClass = $this->getDbTableRowClass($dbTableClass);

        if ($dbTableRowClass === null) {
            return $originalReturnType;
        }

        $rowsetType = new GenericObjectType('Zend_Db_Table_Rowset_Abstract', [
            $dbTableRowClass,
            $dbTableClass
        ]);

        $x = new ObjectType('\SeekableIterator<int,'.$dbTableRowClass->getClassName().'>');

        $resultingType = TypeCombinator::intersect($x, $rowsetType);

        return $resultingType;
    }

    private function getDbTableRowClass(TypeWithClassName $dbTableClass): ?ObjectType
    {
        return $this->rowClassReflector->fromTableClass($dbTableClass);
    }

    private function handleCreateRow(
        Scope $scope,
        MethodCall $methodCall,
        MethodReflection $methodReflection
    ): Type {
        $originalReturnType = $this->getOriginalReturnType($scope, $methodCall, $methodReflection);

        $dbTableClass = $scope->getType($methodCall->var);

        if (!$dbTableClass instanceof TypeWithClassName) {
            return $originalReturnType;
        }

        $dbTableRowClass = $this->getDbTableRowClass($dbTableClass);

        if ($dbTableRowClass === null) {
            return $originalReturnType;
        }

        return $dbTableRowClass;
    }
}
