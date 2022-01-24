<?php

declare(strict_types=1);

namespace App;

use App\MyTable\CarRow;
use ArrayIterator;
use function PHPStan\Testing\assertType;

class TestClass
{
    /** does NOT have db row set */
    private AnimalModel $animalModel;

    /** does have db row set */
    private CarsModel $carsModel;

    public function testThis()
    {
        assertType('App\MyTable\CarRow|null', $this->carsModel->fetchRow());
        // animal model does not have rowClass defined
        assertType('Zend_Db_Table_Row|null', $this->animalModel->fetchRow());

        assertType('SeekableIterator<int,Zend_Db_Table_Row>&Zend_Db_Table_Rowset_Abstract<Zend_Db_Table_Row, App\AnimalModel>', $this->animalModel->fetchAll());
        $specificRowset = $this->carsModel->fetchAll();
        assertType('SeekableIterator<int,App\MyTable\CarRow>&Zend_Db_Table_Rowset_Abstract<App\MyTable\CarRow, App\CarsModel>', $specificRowset);
        assertType('App\MyTable\CarRow|null', $specificRowset->current());
        assertType('array<int, App\MyTable\CarRow>', iterator_to_array($specificRowset));

        // create row
        assertType(CarRow::class, $this->carsModel->createRow());
        assertType('Zend_Db_Table_Row', $this->animalModel->createRow());
    }
}
