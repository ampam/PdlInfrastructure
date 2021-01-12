<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/15/2017
 * Time: 11:04 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


/**
 * Class Column
 * @property string $name
 * @package namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;
 */
class Column
{

    /**
     * Column constructor.
     */
    public function __construct()
    {
        //TODO implement Column
        $this->name = '';
    }

    /**
     * @param Column|String $column
     *
     * @return mixed
     */
    public static function toString( $column )
    {
        $result = $column instanceof Column
            ? $column->name
            : $column;

        $result = "`{$result}`";

        return $result;
    }
}
