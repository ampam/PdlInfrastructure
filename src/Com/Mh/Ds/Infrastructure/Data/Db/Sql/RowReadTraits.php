<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 6:56 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


use Com\Mh\Ds\Infrastructure\Data\Row;
use Com\Mh\Ds\Infrastructure\Data\WhereStatement;
use Com\Mh\Ds\Infrastructure\Data\Db\SqlOptions;

/**
 * Trait RowTraits
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
trait RowReadTraits
{

    /**
     * @return string
     */
    public abstract function getRowClass();

    /**
     * @param $dbId
     * @param FieldList|null $fieldList
     *
     * @return Row
     */
    public function _loadRowById( $dbId, FieldList $fieldList = null )
    {
        $result = Row::loadRow( $dbId, $fieldList, $this->getRowClass() );
        return $result;
    }

    /**
     * @param WhereStatement|null $where
     * @param FieldList|null $fieldList
     *
     * @return Row
     *
     */
    public function _loadRow( WhereStatement $where = null, FieldList $fieldList = null )
    {
        $result = Row::loadRowWhere( $where, $fieldList, $this->getRowClass() );
        return $result;
    }


    /**
     * @param WhereStatement|null $where
     * @param FieldList|null $fieldList
     * @param bool $byDbId
     *
     * @return Row[]
     */
    public function _loadRows( WhereStatement $where = null, FieldList $fieldList = null, $byDbId = false )
    {
        $result = Row::loadRows( [
            SqlOptions::Fields => $fieldList,
            SqlOptions::Where => $where
            ], $byDbId, $this->getRowClass() );

        return $result;
    }


}
