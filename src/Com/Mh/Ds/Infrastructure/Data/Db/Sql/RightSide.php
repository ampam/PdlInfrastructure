<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 4/25/2018
 * Time: 4:58 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;


/**
 * @property  string $value
 */
class RightSide
{

    /**
     * RightSide constructor.
     *
     * @param $value
     */
    public function __construct( $value )
    {
        $this->value = $value;
    }

    /**
     * @param $value
     *
     * @return RightSide
     */
    public static function create( $value )
    {
        $result = new self( $value );

        return $result;

    }
}
