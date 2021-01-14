<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 6:49 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


use Exception;


/**
 * Class WhereStatement
 * @package Com\Mh\Ds\Infrastructure\Data
 */
abstract class WhereStatement extends BoolExpression
{
    use RowReadTraits
    {
        _loadRow as _traitLoadRow;
        _loadRows as _traitLoadRows;
    }

    use RowWriteTraits
    {
        _multiUpdate as _traitMultiUpdate;
        _delete as _traitDelete;
    }

    /** @var FieldList */
    protected $fieldList = null;

    public abstract function getWhereClass();
    public abstract function getFieldListClass();
    public abstract function getOrderByClass();

    /**
     * @return FieldList
     */
    public function _fieldList()
    {
        if ( $this->fieldList == null )
        {
            $class = $this->getFieldListClass();
            $this->fieldList = new $class( $this );
        }
        $result = $this->fieldList;
        return $result;
    }

    /**
     * @return OrderByStatement
     */
    public function _orderBy()
    {
        $class = $this->getOrderByClass();
        $result = new $class( $this, $this->fieldList );
        return $result;
    }

    /**
     * WhereStatement constructor.
     *
     * @param BoolExpression|null $parent
     */
    public function __construct( BoolExpression $parent = null )
    {
        parent::__construct( $parent );
    }

    /**
     * @return mixed
     *
     */
    public function _loadRow()
    {
        return $this->_traitLoadRow( $this );
    }

    /**
     * @return mixed
     */
    public function _delete()
    {
        return $this->_traitDelete( $this );
    }

    /**
     * @param WhereStatement|null $where
     * @param FieldList|null $fieldList
     * @param boolean $byDbId
     *
     * @return mixed
     */
    public function _loadRows( WhereStatement $where = null, FieldList $fieldList = null, $byDbId = false )
    {
        assert( $where === null || $this === $where );
        $this->fieldList = $fieldList ?? $this->fieldList;

        return $this->_traitLoadRows( $this, $this->fieldList, $byDbId );
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function _toWhere()
    {
        $result = "WHERE " . $this->_toString();

        return $result;
    }

}
