<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/7/2017
 * Time: 2:32 AM
 */

namespace Com\Mh\Ds\Infrastructure\Http\Encoders;

use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Languages\Pdl\PdlInjector;


/**
 * Class PdlJsonEncoder
 * @package Com\Mh\Ds\Infrastructure\Http\Encoders
 */
class PdlJsonEncoder implements IJsonEncoder
{
    use SingletonTrait;


    /**
     * @param $object
     *
     * @return string
     */
    public function encode( $object )
    {
        PdlInjector::getInstance()->inject( $object );
        $result = json_encode( $object );
        return $result;
    }


}
