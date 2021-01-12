<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/10/2017
 * Time: 10:37 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php;

use ReflectionObject;
use ReflectionProperty;

/**
 * Class ObjectInjector
 * @package Com\Mh\Ds\Infrastructure\Languages\Php
 */
class ObjectGraphVisitor
{
    const VisitedKey = '__visited__';
    private $visited;

    /** @var IObjectObserver */
    private $observer;

    private $root;

    /**
     * ObjectGraphVisitor constructor.
     *
     * @param mixed $root
     * @param IObjectObserver $observer
     */
    public function __construct( &$root, IObjectObserver $observer )
    {
        $this->root = $root;
        $this->observer = $observer;
        $this->visited = [];
    }

    /**
     *
     */
    public function start()
    {
        if ( self::isVisitable( $this->root ) )
        {
            $this->visit( $this->root );
            $this->cleanUpMarks();
        }
        $this->observer->end( $this->root );
    }

    /**
     * @param mixed $value
     */
    private function visit( &$value )
    {
        if ( !$this->wasVisited( $value ) )
        {
            if ( is_array( $value ) )
            {
                $this->visitArray( $value );
            }
            else if ( is_object( $value ) )
            {
                $this->visitObject( $value );
            }
        }
    }

    /**
     * @param $array
     */
    private function visitArray( &$array )
    {
        $this->markAsVisited( $array );

        foreach ( $array as &$element )
        {
            if ( self::isVisitable( $element ) )
            {
                $this->visit( $element );
            }
            else
            {
                $this->observer->onScalar( $value );
            }
        }
        $this->observer->onArray( $array );
    }

    /**
     * @param $object
     */
    private function visitObject( &$object )
    {
        $this->markAsVisited( $object );

        $reflectionObject = new ReflectionObject( $object );
        $properties = $reflectionObject->getProperties( ReflectionProperty::IS_PUBLIC );
        foreach ( $properties as &$property )
        {
            $propertyName = $property->getName();
            $value =& $object->$propertyName;
            if ( self::isVisitable( $value ) )
            {
                $this->visit( $value );
            }
            else
            {
                $this->observer->onScalar( $value );
            }
        }

        $this->observer->onObject( $object );
    }

    /**
     * @param $arrayOrObject
     *
     * @return bool
     */
    private function wasVisited( &$arrayOrObject )
    {
        $result = ( is_array( $arrayOrObject ) && key_exists( self::VisitedKey, $arrayOrObject ) ) ||
                    is_object( $arrayOrObject ) && property_exists( $arrayOrObject, self::VisitedKey );
        return $result;
    }

    /**
     *
     */
    private function cleanUpMarks()
    {
        $visitedKey = self::VisitedKey;
        foreach( $this->visited as &$arrayOrObject )
        {
            if ( is_object( $arrayOrObject ) )
            {
                unset( $arrayOrObject->$visitedKey );
            }
            else if ( is_array( $arrayOrObject ) )
            {
                unset( $arrayOrObject[ $visitedKey ] );
            }

        }
        $this->visited = [];
    }

    /**
     * @param $arrayOrObject
     */
    private function markAsVisited( &$arrayOrObject )
    {
        $visitedKey = self::VisitedKey;
        if ( is_array( $arrayOrObject ) )
        {
            $arrayOrObject[ self::VisitedKey ] = true;
            $this->visited[] =& $arrayOrObject;
        }
        else if ( is_object( $arrayOrObject ) )
        {
            $arrayOrObject->$visitedKey = true;
            $this->visited[] = $arrayOrObject;
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private static function isVisitable( &$value )
    {
        $result = is_object( $value ) || is_array( $value );
        return $result;
    }

}
