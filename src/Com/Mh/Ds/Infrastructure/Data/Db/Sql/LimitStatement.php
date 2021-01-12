<?php
/**
 * Minglehouse LLC
 * Copyright (c) 2017
 */

/**
 * Created by PhpStorm.
 * User: am
 * Date: 11/12/2017
 * Time: 3:11 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


/**
 * Class LimitStatement
 * @property string $quantity
 * @property string $offset
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
class LimitStatement
{

    /**
     * LimitStatement constructor.
     */
    public function __construct()
    {
        $this->quantity = '';
        $this->offset = '';
    }

    /**
     *
     */
    public function _toString()
    {
        $result = empty( $this->offset )
            ? "LIMIT {$this->quantity}"
            : "LIMIT {$this->offset}, {$this->quantity}";

        return $result;
    }
}
