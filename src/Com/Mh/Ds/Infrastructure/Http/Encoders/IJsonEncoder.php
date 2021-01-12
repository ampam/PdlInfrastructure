<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/7/2017
 * Time: 2:30 AM
 */

namespace Com\Mh\Ds\Infrastructure\Http\Encoders;


interface IJsonEncoder
{
    /**
     * @param $object
     *
     * @return string
     */
    public function encode( $object );
}
