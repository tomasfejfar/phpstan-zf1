<?php

declare(strict_types=1);

namespace App;

use App\MyTable\CarRow;
use function PHPStan\Testing\assertType;

class TestClass
{
    private AnimalModel $animalModel;

    private CarsModel $carsModel;

    public function testThis()
    {
        assertType(CarRow::class . '|null', $this->carsModel->fetchRow());
        // animal model does not have rowClass defined
        assertType('Zend_Db_Table_Row|null', $this->animalModel->fetchRow());

        assertType('iterable<int, Zend_Db_Table_Row>&Zend_Db_Table_Rowset_Abstract', $this->animalModel->fetchAll());
        assertType('iterable<int, ' . CarRow::class . '>&Zend_Db_Table_Rowset_Abstract', $this->carsModel->fetchAll());

        assertType(CarRow::class, $this->carsModel->createRow());
        // animal model does not have rowClass defined
        assertType('Zend_Db_Table_Row', $this->animalModel->createRow());
    }
}
