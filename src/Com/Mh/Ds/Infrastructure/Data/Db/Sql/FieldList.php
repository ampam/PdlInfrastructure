<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 5:58 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

use Com\Mh\Ds\Infrastructure\Data\Row;
use Com\Mh\Ds\Infrastructure\Data\Db\Sql\WhereStatement;
use Exception;

/**
 * Class FieldList
 * @property WhereStatement $where
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
abstract class FieldList extends ColumnsList
{
    use RowReadTraits {
        _loadRow as _traitLoadRow;
        _loadRows as _traitLoadRows;
        _loadRowById as _traitLoadRowById;
    }

    /**
     * FieldList constructor.
     *
     * @param WhereStatement|null $where
     */
    public function __construct( WhereStatement $where = null )
    {
        parent::__construct();
        $this->where = $where;
    }

    public abstract function getOrderByClass();

    /**
     * @return OrderByStatement
     */
    public function _orderBy()
    {
        $class = $this->getOrderByClass();
        $result = new $class( $this->where, $this );
        $result->rowClass = $this->getRowClass();
        return $result;
    }


    /**
     * @return Row
     * @throws Exception
     */
    public function _loadRowWhere()
    {
        if ( $this->where == null )
        {
            throw new Exception( "No where clause found" );
        }

        $result = Row::loadRowWhere(
            $this->where,
            $this->_getColumns(),
            $this->getRowClass() );

        return $result;
    }


    /**
     * @return string
     */
    public abstract function getRowClass();

}
