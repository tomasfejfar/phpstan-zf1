<?php

declare(strict_types=1);

namespace PhpStanZF1\Zend\Db;

class Definitions
{
    public static function getDefaultRowFQCN(): string
    {
        return '\Zend_Db_Table_Row_Abstract';
    }

    public static function getDefaultRowsetFQCN(): string
    {
        return '\Zend_Db_Table_Rowset_Abstract';
    }

    public static function getDefaultTableFQCN(): string
    {
        return '\Zend_Db_Table_Abstract';
    }
}
