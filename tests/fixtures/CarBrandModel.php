<?php

declare(strict_types=1);

namespace App;

use App\CarBrandRow;
use Zend_Db_Table_Abstract;

class CarBrandModel extends Zend_Db_Table_Abstract
{
    protected $_rowClass = CarBrandRow::class;

    protected $_dependentTables = [CarsModel::class];
}
