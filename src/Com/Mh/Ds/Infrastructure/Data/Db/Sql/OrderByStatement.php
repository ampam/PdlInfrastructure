<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 5:05 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

use Com\Mh\Ds\Infrastructure\Data\Row;
use Com\Mh\Ds\Infrastructure\Data\WhereStatement;
use Exception;
use Com\Mh\Ds\Infrastructure\Data\Db\SqlOptions;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;

/**
 * Class OrderByStatement
 * @property WhereStatement $where
 * @property FieldList $fieldList
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 *
 * @uses OrderByStatement::_direction()
 */
abstract class OrderByStatement extends ColumnsList implements ISqlFragment
{
    use RowReadTraits
    {
        _loadRow as _traitLoadRow;
        _loadRows as _traitLoadRows;
    }

    const ASC = '__ASC__';
    const DESC = '__DESC__';

    public static $keywords = [
        self::ASC => 'ASC',
        self::DESC => 'DESC'
    ];

    private $prevToken;
    private $parts;

    /**
     * OrderByStatement constructor.
     *
     * @param WhereStatement|null $where
     * @param FieldList|null $fieldList
     */
    public function __construct( WhereStatement $where = null, FieldList $fieldList = null )
    {
        parent::__construct();
        $this->where = $where;
        $this->fieldList = $fieldList;
    }


    /**
     * @return $this
     */
    public function _asc()
    {
        $this->_addKeyword( self::ASC );
        return $this;
    }

    /**
     * @return $this
     */
    public function _desc()
    {
        $this->_addKeyword( self::DESC );
        return $this;
    }

    /**
     * @param string $sqlDirection
     *
     * @return $this
     */
    public function _direction( $sqlDirection )
    {
        $this->_ascending( $sqlDirection === self::ASC );
        return $this;
    }

    /**
     * @param bool $isAscending
     *
     * @return $this
     */
    public function _ascending( $isAscending )
    {
        $result = $isAscending
            ? $this->_asc()
            : $this->_desc();

        return $result;
    }

    /**
     * @param $keyword
     *
     */
    private function _addKeyword( $keyword )
    {
        $this->_stack[] = ( $keyword );
    }

    /**
     * @param $string
     * @param OrderByStatement $orderByStatement
     *
     * @throws Exception
     *
     */
    public static function throwException( $string, OrderByStatement $orderByStatement )
    {
        Debug::log( $string );
        Debug::log( "orderByStatement stack: " );
        Debug::log( $orderByStatement->_stack );
        throw new Exception( $string );
    }

    /**
     * @return string
     * @throws Exception
     */
    public function _toString()
    {
        $this->parts = [];
        $this->prevToken = null;

        foreach ( $this->_stack as $token )
        {
            $this->processToken( $token );
        }

        $result = implode( ',', $this->parts );
        return $result;

    }

    /**
     * @return string
     * @throws Exception
     */
    public function _toOrderBy()
    {
        $result = 'ORDER BY ' . $this->_toString();
        return $result;
    }

    /**
     * @param $token
     *
     * @throws Exception
     */
    private function processToken( $token )
    {
        switch ( true )
        {
            case $token === self::DESC:
            case $token === self::ASC:
                if ( $this->prevToken != null )
                {
                    $lastValue = array_pop( $this->parts ) . ' ' . self::$keywords[ $token ];
                    $this->parts[] = $lastValue;
                }
                else
                {
                    self::throwException( "Invalid Order By Statement", $this );
                }
                $this->prevToken = null;
                break;

            default:
                $this->parts[] = $token;
                $this->prevToken = $token;
                break;
        }
    }

    /**
     * @param WhereStatement|null $where
     * @param FieldList|null $fieldList
     * @param bool $byDbId
     *
     * @return Row[]
     * @throws Exception
     */
    public function _loadRows( WhereStatement $where = null, FieldList $fieldList = null, $byDbId = false )
    {
        $this->where = $where ??  $this->where;
        $this->fieldList = $fieldList ??  $this->fieldList;

        $result = Row::loadRows( [
            SqlOptions::Fields => $this->fieldList,
            SqlOptions::Where => $this->where,
            SqlOptions::Order => 'ORDER BY ' . $this->_toString()
        ], $byDbId, $this->getRowClass() );
        return $result;
    }

    /**
     * @return string
     */
    public abstract function getRowClass();

}
