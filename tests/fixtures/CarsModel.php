<?php

declare(strict_types=1);

namespace App;

use App\CarRow;
use Zend_Db_Table_Abstract;

class CarsModel extends Zend_Db_Table_Abstract
{
    protected $_rowClass = CarRow::class;

    protected $_referenceMap = [
        'brand' => [
            'columns' => 'brandId',
            'refTableClass' => CarBrandModel::class,
            'refColumns' => 'id',
        ],
    ];
}
