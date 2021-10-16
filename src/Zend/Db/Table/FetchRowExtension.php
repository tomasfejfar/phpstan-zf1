<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db\Table;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use Zend_Db_Table_Abstract;
use const PHP_VERSION_ID;

class FetchRowExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Zend_Db_Table_Abstract::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'fetchRow';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $originalReturnType = $this->getOriginalReturnType($scope, $methodCall, $methodReflection);

        $dbTableClass = $scope->getType($methodCall->var);

        if (!$dbTableClass instanceof TypeWithClassName) {
            return $originalReturnType;
        }

        $nativeReflection = $dbTableClass->getClassReflection()->getNativeReflection();
        if (!$nativeReflection->hasProperty('_rowClass')) {
            return $originalReturnType;
        }

        if (PHP_VERSION_ID <= 80000) {
            $rowClassName = $nativeReflection->getDefaultProperties()['_rowClass'] ?? null;
        } else {
            $rowClassProperty = $nativeReflection->getProperty('_rowClass');
            $rowClassName = $rowClassProperty->getDefaultValue();
        }

        if (!$rowClassName) {
            // row class is not defined
            return $originalReturnType;
        }

        return TypeCombinator::union(new ObjectType($rowClassName), new NullType());
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
        $parameters = $variant->getParameters();
        $originalReturnType = $variant->getReturnType();
        return $originalReturnType;
    }
}
