<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 12/23/2018
 * Time: 11:42 PM
 */

namespace Com\Mh\Ds\Infrastructure\Cache;

use Memcached;


/**
 * Class Cache
 * @package Com\Mh\Ds\Infrastructure\Cache
 */
class Cache
{

    private static $isEnabled = false;

    /** @var ICache */
    private static $cacheImpl = null;

    //private static $config

    /**
     * @param $config
     */
    public static function init( &$config )
    {
        assert( self::$cacheImpl === null );


        self::$isEnabled = !empty( $config[ 'enabled' ] );
        self::create( $config );
    }

    /**
     * @param $config
     */
    private static function create( &$config )
    {
        if ( self::$isEnabled )
        {
            self::$cacheImpl = new MemoryCache( $config );
        }
        else
        {
            self::$cacheImpl = new DryCache();
        }
    }

    /**
     * TODO think this better
     * @return Memcached
     */
    public static function getMemcached()
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->getMemcached();
        return $result;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get( string $key )
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->get( $key );
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return bool
     */
    public static function set( string $key, $value, $expiration = 0 )
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->set( $key, $value, $expiration );
        return $result;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return bool
     */
    public static function replace( string $key, $value, $expiration = 0 )
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->replace( $key, $value, $expiration );
        return $result;
    }

    /**
     * @param string $key
     * @param int $time
     *
     * @return bool
     */
    public static function delete( string $key, $time = 0 )
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->delete( $key, $time );
        return $result;
    }

    /**
     * @param string[] $keys
     * @param int $time
     *
     * @return array
     */
    public static function deleteMulti( array $keys, $time = 0 )
    {
        assert( self::$cacheImpl !== null );
        $result = self::$cacheImpl->deleteMulti( $keys, $time );
        return $result;
    }
}
