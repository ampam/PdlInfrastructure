<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/10/2017
 * Time: 10:37 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Pdl;

use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Languages\LanguageUtils;
use Com\Mh\Ds\Infrastructure\Languages\Php\Boolean;
use Com\Mh\Ds\Infrastructure\Languages\Php\IObjectObserver;
use Com\Mh\Ds\Infrastructure\Languages\Php\ObjectGraphVisitor;
use ReflectionObject;

/**
 * Class ObjectInjector
 * @package Com\Mh\Ds\Infrastructure\Languages\Pdl
 */
class PdlInjector implements IObjectObserver
{
    use SingletonTrait;

    const UnknownType = 'unknown';
    const TypeHint = '__typeHint';
    const Hinted = '__hinted';

    const PropertyAttribute = '_propertyAttribute';

    /**
     * @param $object
     */
    public function inject( &$object )
    {
        $visitor = new ObjectGraphVisitor( $object, $this );
        $visitor->start();
    }

    /**
     * @param $object
     *
     */
    private static function markAsHinted( &$object )
    {
        $hinted = self::Hinted;

        if ( is_object( $object ) )
        {
            $object->$hinted = Boolean::ShortYes;
        }
        else if ( is_array( $object ) )
        {
            $object[ $hinted ] = Boolean::ShortYes;
        }
    }

    /**
     * @param $object
     * @param $phpClassname
     *
     * @phan-suppress PhanUndeclaredProperty
     *
     */
    public static function setTypeHint( &$object, $phpClassname )
    {
        $pdlClassname = LanguageUtils::php2PdlClassname( $phpClassname );
        if ( !LanguageUtils::isProjectClass( $pdlClassname ) )
        {
            $pdlClassname = self::UnknownType;
        }
        $typeHint = self::TypeHint;

        assert( property_exists( $object, $typeHint ) );

        $object->$typeHint = $pdlClassname;
    }

    /**
     * @param $object
     *
     * @phan-suppress PhanUndeclaredProperty
     */
    public function onObject( &$object )
    {
        if ( $object instanceof IPdlInjectable )
        {
            self::setTypeHint( $object, $object->getPdlClassName() );
        }
        else
        {
            $reflectionObject = new ReflectionObject( $object );
            self::setTypeHint( $object, $reflectionObject->name );
        }

        if ( property_exists( $object, self::PropertyAttribute ) )
        {
            $propertyAttribute = self::PropertyAttribute;

            unset( $object->$propertyAttribute );
        }
    }

    /**
     * @param $array
     *
     * nothing to do
     */
    public function onArray( &$array )
    {
    }

    /**
     * @param $value
     *
     * nothing to do
     */
    public function onScalar( &$value )
    {
    }

    /**
     * @param $value
     */
    public function end( &$value )
    {
        self::markAsHinted( $object );
    }
}
