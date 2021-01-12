<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 9/28/2017
 * Time: 2:41 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php;

/**
 * Interface IObjectObserver
 * @package Com\Ms\Ds\Infrastructure\Languages\Php
 */
interface IObjectObserver
{
    function onObject( &$object );
    function onArray( &$array );
    function onScalar( &$value );
    function end( &$value );
}
