<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/18/2017
 * Time: 2:15 AM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Pdl;

use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;
use Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\PropertyUtils;


/**
 * Class PdlRequestDecoder
 * @package Com\Mh\Ds\Infrastructure\Languages\Pdl
 */
class PdlDecoder
{
    const TypeHint = '__type';
    const PdlTypeHint = '__typeHint';

    private static $excludedRequestMembers = [];
    private static $excludedClasses = [];

    /**
     * @param $config
     */
    public static function setConfig( $config )
    {
        self::$excludedRequestMembers = $config['excludedRequestMembers'] ?? [];
        self::$excludedClasses = $config['excludedClasses'] ?? [];
    }


    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function decode( &$value )
    {
        $result = self::decodeValue( $value );
        return $result;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function decodeValue( &$value )
    {
        if ( is_object( $value ) )
        {
            $result = self::decodeObject( $value );
        }
        else if ( is_array( $value ) )
        {
            $result = self::decodeArray( $value );
        }
        else
        {
            $result = $value;
        }
        return $result;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    private static function decodeObject( &$object )
    {
        $result = $object;

        $class = self::getObjectClass( $object );
        if ( !empty( $class ) )
        {
            $result = new $class();
        }

        self::decodeMembers( $result, $object );
        return $result;
    }

    /**
     * @param $array
     *
     * @return mixed
     */
    private static function decodeArray( &$array )
    {
        $result = $array;
        $class = self::getArrayClass( $array );
        if ( !empty( $class ) )
        {
            $result = new $class();
            self::decodeMembers( $result, $array );
        }
        else
        {
            self::decodeMembersToArray( $result, $array );
        }

        return $result;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public static function isArrayHinted( &$array )
    {
        $result = ( array_key_exists( self::TypeHint, $array ) && !empty( $array[ self::TypeHint ] ) ) ||
            ( array_key_exists( self::PdlTypeHint, $array ) && !empty( $array[ self::PdlTypeHint ] ) );
        return $result;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public static function isObjectHinted( $object )
    {
        $typeHint = self::TypeHint;
        $pdlTypeHint = self::PdlTypeHint;
        $result = ( property_exists( $object, self::TypeHint ) && !empty( $object->$typeHint ) ) ||
            ( property_exists( $object, self::PdlTypeHint ) && !empty( $object->$pdlTypeHint ) );
        return $result;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isHinted( &$value )
    {
        $result = false;
        if ( is_array( $value ) )
        {
            $result = self::isArrayHinted( $value );
        }
        else if ( is_object( $value ) )
        {
            $result = self::isObjectHinted( $value );
        }

        return $result;
    }


    /**
     * @param array $array
     *
     * @return string
     */
    public static function getArrayClass( $array )
    {
        $result = null;

        if ( array_key_exists( self::TypeHint, $array ) )
        {
            $className = self::convert2PhpClassName( $array[ self::TypeHint ] );
            $result = self::getClass( $className );
        }

        if ( $result === null && array_key_exists( self::PdlTypeHint, $array ) )
        {
            $pdlClassName = self::convert2PhpClassName( $array[ self::PdlTypeHint ] );

            if ( class_exists( $pdlClassName ) )
            {
                $result = $pdlClassName;
            }
        }

        return $result;
    }


    /**
     * @param Object $object
     *
     * @return string
     */
    public static function getObjectClass( $object )
    {
        $result = null;

        if ( property_exists( $object, self::TypeHint ) )
        {
            $typeHint = self::TypeHint;
            $className = self::convert2PhpClassName( $object->$typeHint );
            $result = self::getClass( $className );
        }

        if ( empty( $result ) && property_exists( $object, self::PdlTypeHint ) )
        {
            $pdlTypeHint = self::PdlTypeHint;
            $pdlClassName = self::convert2PhpClassName( $object->$pdlTypeHint );

            if ( class_exists( $pdlClassName ) )
            {
                $result = $pdlClassName;
            }
        }

        return $result;
    }

    /**
     * @param $object
     * @param string $memberName
     *
     * @return bool
     */
    private static function canDecodeMember( &$object, string $memberName )
    {
        $isExcludedRequestMember = $object instanceof BaseRequest &&
            in_array( $memberName, self::$excludedRequestMembers );

        $result = $memberName !== self::TypeHint && !$isExcludedRequestMember;

        return $result;
    }

    /**
     * @param Object $object
     * @param Object|array $arrayOrObject
     */
    private static function decodeMembers( &$object, &$arrayOrObject )
    {
        foreach ( $arrayOrObject as $memberName => &$value )
        {
            if ( self::canDecodeMember( $object, $memberName ) )
            {
                self::assignProperty( $object, $memberName, $value );
            }
        }
    }

    /**
     * @param array $array
     * @param Object|array $arrayOrObject
     */
    private static function decodeMembersToArray( &$array, &$arrayOrObject )
    {
        foreach ( $arrayOrObject as $memberName => &$value )
        {
            if ( self::canDecodeMember( $object, $memberName ) )
            {
                $array[ $memberName ] = self::decodeValue( $value );
            }
        }
    }

    /**
     * @param $object
     * @param $memberName
     * @param $value
     */
    private static function assignProperty( $object, $memberName, $value )
    {
        if ( is_scalar( $value ) )
        {
            PropertyUtils::assignScalarProperty( $object, $memberName, $value );
        }
        else
        {
            $object->$memberName = self::decodeValue( $value );
        }
    }


    /**
     * @param $pdlClassName
     *
     * @return string
     */
    public static function convert2PhpClassName( $pdlClassName )
    {
        $pdlClassName = str_replace( '::', '.', $pdlClassName );
        $parts = array_map( 'ucfirst', explode( '.', $pdlClassName ) );
        $result = implode( '\\', $parts );
        return $result;
    }

    /**
     * @param $className
     *
     * @return string
     */
    public static function getClass( $className )
    {
        if ( class_exists( $className ) )
        {
            $result = $className;
        }
        else
        {
            $result = "{$className}Base";
            if ( !class_exists( $result ) )
            {
                Debug::log( "Can't find Pdl Class:{$className} or {$result}" );
                $result = null;
            }
        }

        return $result;

    }


}
