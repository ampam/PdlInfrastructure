<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/15/2017
 * Time: 11:29 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php;

/**
 * Class PhpUtils
 * @package Com\Mh\Ds\Infrastructure\Languages\Php
 */
class PhpUtils
{
    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValueType( $value )
    {
        $result = !is_object( $value );
        return $result;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public static function isStringValueType( $type )
    {
        $result = false;
        switch ( strtolower( $type ) )
        {
            case 'bool':
            case 'boolean':
            case 'string':
            case 'float':
            case 'int':
                $result = true;
                break;

            default:
                break;
        }
        return $result;
    }

    /**
     * @param $key
     * @param $array
     *
     * @return bool
     */
    public static function keyExists( $key, $array )
    {
        $result = ( is_string( $key ) || is_int( $key ) )
            && key_exists( $key, $array );

        return $result;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isScalar( &$value )
    {
        $result = is_scalar( $value );
        return $result;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isBool( &$value )
    {
        $result = is_bool( $value );
        return $result;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isRefType( $value )
    {
        $result = is_object( $value );
        return $result;
    }

}
