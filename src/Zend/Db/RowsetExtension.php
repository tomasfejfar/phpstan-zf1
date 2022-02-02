<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

use LogicException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateGenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use const PHP_VERSION_ID;

class RowsetExtension implements DynamicMethodReturnTypeExtension
{
    const METHOD_CURRENT = 'current';

    private RowClassReflector $rowClassReflector;

    public function __construct()
    {
        $this->rowClassReflector = new RowClassReflector();
    }

    public function getClass(): string
    {
        return 'Zend_Db_Table_Rowset_Abstract';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), $this->supportedMethods());
    }

    /** @return string[] */
    protected function supportedMethods(): array
    {
        return [
            self::METHOD_CURRENT
        ];
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        if ($methodReflection->getName() === self::METHOD_CURRENT) {
            return $this->handleCurrent($scope, $methodCall, $methodReflection);
        }
        throw new LogicException(sprintf('Unsupported method "%s"', $methodReflection->getName()));
    }

    private function handleCurrent(Scope $scope, MethodCall $methodCall, MethodReflection $methodReflection): Type
    {
        $rowsetType = $scope->getType($methodCall->var);

        if ($rowsetType instanceof IntersectionType) {
            $types = $rowsetType->getTypes();
            $typeZendRowset = new ObjectType(Definitions::getDefaultRowsetFQCN());
            $zendDbRowsetType = array_reduce($types, function (?Type $carry, Type $type) use ($typeZendRowset) {
                if ($typeZendRowset->isSuperTypeOf($type)) {
                    return $type;
                }
                return $carry;
            }, null);
            $rowsetType = $zendDbRowsetType;
        }

        $rowClass = $this->rowClassReflector->fromRowsetClass($rowsetType);
        return TypeCombinator::union($rowClass, new NullType());
    }
}
