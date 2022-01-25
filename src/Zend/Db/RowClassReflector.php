<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

use LogicException;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

class RowClassReflector
{
    const PROPERTY_ROW_CLASS = '_rowClass';

    public function fromTableClass(TypeWithClassName $dbTableClass): ObjectType
    {
        $tableSupertype = new ObjectType(Definitions::getDefaultTableFQCN());
        if (!$tableSupertype->isSuperTypeOf($dbTableClass)) {
            throw new LogicException('Cannot extract rowclass from something that is not a table');
        }

        $nativeReflection = $dbTableClass->getClassReflection()->getNativeReflection();
        if (!$nativeReflection->hasProperty(self::PROPERTY_ROW_CLASS)) {
            return new ObjectType(Definitions::getDefaultRowFQCN());
        }

        if (PHP_VERSION_ID <= 80000) {
            $rowClassName = $nativeReflection->getDefaultProperties()[self::PROPERTY_ROW_CLASS] ?? null;
        } else {
            $rowClassProperty = $nativeReflection->getProperty(self::PROPERTY_ROW_CLASS);
            $rowClassName = $rowClassProperty->getDefaultValue();
        }

        if (!$rowClassName) {
            // row class is not defined
            return new ObjectType(Definitions::getDefaultRowFQCN());
        }

        return new ObjectType($rowClassName);
    }

    public function fromRowsetClass(Type $rowsetClass): Type
    {
        if (!$rowsetClass instanceof GenericObjectType) {
            return new ObjectType(Definitions::getDefaultRowFQCN());
        }

        $rowType = $rowsetClass->getTypes()[0];

        return $rowType;
    }
}
