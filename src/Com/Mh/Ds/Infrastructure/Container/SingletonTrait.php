<?php


namespace Com\Mh\Ds\Infrastructure\Container;

/**
 * Trait SingletonTrait
 * @package Com\Mh\Ds\Infrastructure\Container
 */
trait SingletonTrait
{

    protected static $instance;

    /**
     * @return static
     */
    final public static function getInstance()
    {
        $result = isset( static::$instance )
            ? static::$instance
            : static::$instance = new static;

        return $result;
    }

//    final private function __construct() {
//        $this->init();
//    }
//    protected function init() {}
//    final private function __wakeup() {}
//    final private function __clone() {}

}
