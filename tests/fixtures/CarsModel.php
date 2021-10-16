<?php

declare(strict_types=1);

namespace App;

use App\MyTable\CarRow;
use Zend_Db_Table_Abstract;

class CarsModel extends Zend_Db_Table_Abstract
{
    protected $_rowClass = CarRow::class;
}
