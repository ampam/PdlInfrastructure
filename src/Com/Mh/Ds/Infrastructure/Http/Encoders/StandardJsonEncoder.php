<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/7/2017
 * Time: 2:31 AM
 */

namespace Com\Mh\Ds\Infrastructure\Http\Encoders;

use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;


/**
 * Class StandardJsonEncoder
 * @package Com\Mh\Ds\Infrastructure\Http\Encoders
 */
class StandardJsonEncoder implements IJsonEncoder
{
    use SingletonTrait;

    /**
     * @param $object
     *
     * @return string
     */
    public function encode( $object )
    {
        $result = json_encode( $object );
        return $result;
    }
}
