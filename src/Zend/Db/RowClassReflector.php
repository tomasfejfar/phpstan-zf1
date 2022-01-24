<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

use LogicException;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateTypeVariance;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use function PHPStan\dumpType;

class RowClassReflector
{
    public function fromTableClass(TypeWithClassName $dbTableClass): ObjectType
    {
        $tableSupertype = new ObjectType('Zend_Db_Table_Abstract');
        if (!$tableSupertype->isSuperTypeOf($dbTableClass)) {
            throw new LogicException('Cannot extract rowclass from something that is not a table');
        }

        $nativeReflection = $dbTableClass->getClassReflection()->getNativeReflection();
        if (!$nativeReflection->hasProperty('_rowClass')) {
            return new ObjectType('Zend_Db_Table_Row');
        }

        if (PHP_VERSION_ID <= 80000) {
            $rowClassName = $nativeReflection->getDefaultProperties()['_rowClass'] ?? null;
        } else {
            $rowClassProperty = $nativeReflection->getProperty('_rowClass');
            $rowClassName = $rowClassProperty->getDefaultValue();
        }

        if (!$rowClassName) {
            // row class is not defined
            return new ObjectType('Zend_Db_Table_Row');
        }

        return new ObjectType($rowClassName);
    }

    public function fromRowsetClass(Type $rowsetClass): Type
    {
        if (!$rowsetClass instanceof GenericObjectType) {
            return new ObjectType('Zend_Db_Table_Row');
        }

        $rowType = $rowsetClass->getTypes()[0];

        return $rowType;
    }
}
