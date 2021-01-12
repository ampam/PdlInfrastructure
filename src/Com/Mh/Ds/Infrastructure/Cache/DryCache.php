<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 12/24/2018
 * Time: 3:39 PM
 */

namespace Com\Mh\Ds\Infrastructure\Cache;

use Memcached;

/**
 * Class DryCache
 * @package Com\Mh\Ds\Infrastructure\Cache
 */
class DryCache implements ICache
{

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get( string $key )
    {
        return null;
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
        return true;
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
        return true;
    }

    /**
     * @param string $key
     * @param int $time
     *
     * @return bool
     */
    public static function delete( string $key, $time = 0 )
    {
        return true;
    }

    /**
     * @return Memcached
     */
    public function getMemcached()
    {
        return null;
    }

    /**
     * @param string[] $keys
     * @param int $time
     *
     * @return array
     */
    public function deleteMulti( array $keys, $time = 0 )
    {
        return [];
    }
}
