<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 7/19/2016
 * Time: 12:10 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data;

use Com\Mh\Ds\Infrastructure\Data\Db\IDbOperations;

/**
 * Interface IRowFactory
 * @package Com\Mh\Ds\Infrastructure\Data
 */
interface IRowFactory
{
    /**
     * @param $rowClass
     *
     * @return Row
     */
    public function create( $rowClass );

    /**
     * @return IDbOperations
     */
    public function getDb():IDbOperations;

    public function setLog( bool $value);
    public function getLog():bool;
}
