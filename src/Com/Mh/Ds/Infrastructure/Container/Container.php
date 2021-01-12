<?php


namespace Com\Mh\Ds\Infrastructure\Container;

/**
 * Class Container
 * @package Com\Mh\Ds\Infrastructure\Container
 */
class Container
{
    private static $buckets = [];

    /**
     * @param mixed ...$arguments
     *
     * @return object
     */
    public static function getInstance( ...$arguments )
    {
        $className = $arguments[ 0 ];
        assert( class_exists( $className, true ), "Invalid class passed to getInstance: {$className}" );


        $classBucket = [];

        if ( empty( self::$buckets[ $className ] ) )
        {
            self::$buckets[ $className ] =& $classBucket;
        }
        else
        {
            $classBucket = &self::$buckets[ $className ];
        }

        if ( empty( $classBucket[ 'instance' ] ) )
        {
            $result = new $className();
            $classBucket[ 'instance' ] = $result;

            if ( method_exists( $result, 'init' ) )
            {
                if ( isset( $arguments[ 1 ] ) )
                {
                    $result->init( $arguments[ 1 ] );
                }
                else
                {
                    $result->init();
                }
            }
        }
        else
        {
            $result = $classBucket[ 'instance' ];
        }

        return $result;

    }
}
