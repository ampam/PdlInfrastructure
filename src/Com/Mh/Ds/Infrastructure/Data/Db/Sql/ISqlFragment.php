<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/17/2017
 * Time: 6:34 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

/**
 * Interface ISqlFragment
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
interface ISqlFragment
{
    /**
     * @return string
     */
    function _toString();
}
