<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/16/2017
 * Time: 1:37 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data;

/**
 * Trait ColumnBase
 * @package Com\Mh\Ds\Infrastructure\Data
 */
trait ColumnsBase
{

    /**
     * @param $column
     * @param null $param
     *
     * @return mixed
     */
    protected  function _useColumn( $column, /** @noinspection PhpUnusedParameterInspection */ $param = null )
    {
        return $column;
    }

}
