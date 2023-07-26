<?php

declare(strict_types=1);

namespace Tests\Zend\Db;

use App\CarsModel;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PhpStanZF1\Zend\Db\TableReferenceReflector;
use PHPUnit\Framework\TestCase;
use Zend_Db_Table_Abstract;

class TableReferenceReflectorTest extends PHPStanTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $reflectionProvider = $this->createReflectionProvider();
    }

    public function testFromTableClass(): void
    {
        $ref = new TableReferenceReflector();
        $tableType = new ObjectType(CarsModel::class);

        $referenceMap = $ref->fromTableClass($tableType);
        $this->assertSame(
            [
                'brand' => [
                    'columns' => 'brandId',
                    'refTableClass' => 'App\CarBrandModel',
                    'refColumns' => 'id',
                ],
            ],
            $referenceMap
        );
    }
}
