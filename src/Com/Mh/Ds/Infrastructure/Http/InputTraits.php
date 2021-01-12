<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 3/12/2016
 * Time: 3:35 PM
 */

namespace Com\Mh\Ds\Infrastructure\Http;


/**
 * Class InputTraits
 * @package Com\Mh\Ds\Infrastructure\Http
 */
trait InputTraits
{

    /**
     * @param $name
     *
     * @return bool
     */
    public static function exist( $name )
    {
        $result = isset( $_REQUEST[ $name ] );
        return $result;
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return bool|mixed
     */
    public static function get( $name, $default = false )
    {
        $result = isset( $_REQUEST[ $name ] )
            ? $_REQUEST[ $name ]
            : $default;

        return $result;
    }

    /**
     * @param string $name
     * @param $default
     *
     * @return bool|mixed
     */
    public static function getObject( string $name, $default = null )
    {
        $result = $_REQUEST[ $name ] ?? $default;

//        assert( $result === null || is_object( $result ), "Expecting Object in request" );

        return $result;
    }

    /**
     * @param string $name
     * @param int $default
     *
     * @return int
     */
    public static function getInt( string $name, $default = 0 )
    {
        $result = (int)self::get( $name, $default );
        return $result;
    }

    /**
     * @param $name
     *
     * @param array $default
     *
     * @return array
     */
    public static function getArray( $name, $default = [] )
    {
        $result = self::get( $name, $default );
        return $result;
    }

    /**
     * @param $name
     *
     * @param string $default
     *
     * @return string
     */
    public static function getString( $name, $default = '' )
    {
        $result = self::get( $name, $default );
        return $result;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public static function set( $name, $value )
    {
        $_REQUEST[ $name ] = $value;
    }
}
