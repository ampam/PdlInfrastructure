<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 12/24/2018
 * Time: 3:44 PM
 */

namespace Com\Mh\Ds\Infrastructure\Cache;


use Memcached;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;

/**
 * Class MemoryCache
 * @package Com\Mh\Ds\Infrastructure\Cache
 */
class MemoryCache implements ICache
{
    /** @var Memcached */
    private static $memcached = null;

    private static $config;

    /**
     * MemoryCache constructor.
     *
     * @param $config
     */
    public function __construct( $config )
    {
        assert( self::$memcached === null );
        self::$config = $config;

        self::$memcached = new Memcached( self::$config[ 'persistentId' ] );

        self::$memcached->setOptions( [
            self::$config[ 'options' ]
        ] );

        foreach ( self::$config[ 'servers' ] as $serverName => $server )
        {
            self::$memcached->addServer( $serverName, $server[ 'port' ] );
        }
    }


    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get( string $key )
    {
        assert( self::$memcached !== null );
        $result = self::$memcached->get( $key );
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
        assert( self::$memcached !== null );
        $result = self::$memcached->set( $key, $value, $expiration );
        if ( $result === false )
        {
            self::logMemcachedError();
        }
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
        assert( self::$memcached !== null );
        $result = self::$memcached->replace( $key, $value, $expiration );
        if ( $result === false )
        {
            self::logMemcachedError();
        }
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
        assert( self::$memcached !== null );
        $result = self::$memcached->delete( $key, $time );
        if ( $result === false )
        {
            self::logMemcachedError();
        }
        return $result;
    }

    /**
     *
     */
    private static function logMemcachedError()
    {
        $errorCode = self::$memcached->getResultCode();
        $message = self::$memcached->getResultMessage();
        Debug::log( "Memcached Error: {$errorCode}, msg: {$message} " );
    }

    /**
     * @return Memcached
     */
    public function getMemcached()
    {
        return self::$memcached;
    }

    /**
     * @param string[] $keys
     * @param int $time
     *
     * @return array
     */
    public function deleteMulti( array $keys, $time = 0 )
    {
        $result = self::$memcached->deleteMulti( $keys, $time );
        return $result;
    }
}
