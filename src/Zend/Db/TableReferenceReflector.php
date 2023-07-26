<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

use LogicException;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;

class TableReferenceReflector
{
    const PROPERTY_REF_MAP = '_referenceMap';

    public function fromTableClass(TypeWithClassName $dbTableClass): array
    {
        $tableSupertype = new ObjectType(Definitions::getDefaultTableFQCN());
        if (!$tableSupertype->isSuperTypeOf($dbTableClass)) {
            throw new LogicException('Cannot extract reference map from something that is not a table');
        }



        $nativeReflection = $dbTableClass->getClassReflection()->getNativeReflection();
        if (!$nativeReflection->hasProperty(self::PROPERTY_REF_MAP)) {
            return [];
        }

        if (PHP_VERSION_ID <= 80000) {

            $referenceMap = $nativeReflection->getDefaultProperties()[self::PROPERTY_REF_MAP] ?? null;
        } else {
            $refMapProperty = $nativeReflection->getProperty(self::PROPERTY_REF_MAP);
            $referenceMap = $refMapProperty->getDefaultValue();
        }

        /** @var string|null $referenceMap  */
        if (!$referenceMap) {
            // row class is not defined
            return [];
        }

        return $referenceMap;
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
