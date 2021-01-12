<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/16/2017
 * Time: 2:09 AM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

/**
 * Class ColumnList
 * @package Com\Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
class ColumnsList
{
    /** @var string[] */
    protected $_stack;


    /**
     * ColumnList constructor.
     *
     */
    public function __construct()
    {
        $this->_stack = [];
    }

    /**
     * @param Column|String $column
     *
     * @return $this
     */
    public function _add( $column )
    {
        $columnName = $column instanceof Column
            ? $column->name
            : $column;

        $this->_stack[] = $columnName;

        return $this;
    }

    /**
     * @return string[]
     */
    public function _getColumns()
    {
        $result = $this->_stack;
        return $result;
    }

    /**
     * @return string[]
     */
    public function _getCleanColumns()
    {
        $result = str_replace( '`', '', $this->_stack );
        return $result;
    }


}
