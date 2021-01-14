<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 6:56 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


use Com\Mh\Ds\Infrastructure\Data\Row;
use Com\Mh\Ds\Infrastructure\Data\Db\SqlOptions;

/**
 * Trait RowTraits
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
trait RowWriteTraits
{

    /**
     * @return string
     */
    public abstract function getRowClass();
    public abstract function fullTableName();

    /**
     * @param WhereStatement|null $where
     *
     * @return int
     */
    public function _delete( WhereStatement $where = null )
    {
        $result = Row::deleteWhere( [
            SqlOptions::Where => $where
        ], $this->fullTableName() );

        return $result;
    }


    /**
     * @param array $fieldArray
     * @param WhereStatement|null $where
     *
     * @return int
     */
    public function _updateFromArray( array $fieldArray, WhereStatement $where = null )
    {
        $result = Row::multiUpdate( [
            SqlOptions::Fields => $fieldArray,
            SqlOptions::Where => $where
        ], $this->fullTableName() );

        return $result;
    }
}
