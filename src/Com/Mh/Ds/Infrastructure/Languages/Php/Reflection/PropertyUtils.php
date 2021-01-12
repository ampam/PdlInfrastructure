<?php
/**
 * Created by PhpStorm.
 * User: rnunez
 * Date: 11/7/2018
 * Time: 3:05 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection;


use Com\Mh\Ds\Infrastructure\Languages\Php\Boolean;
use Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\DocBlockUtils;

/**
 * Class PropertyUtils
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection
 */
class PropertyUtils
{
    /**
     * @param Object $object
     * @param string $propertyName
     * @param mixed $value
     */
    public static function assignScalarProperty( $object, $propertyName, $value )
    {
        assert( is_scalar( $value ), "assignScalarProperty value is not scalar" );
        $type = DocBlockUtils::getPropertyTypeAsString( $object, $propertyName );
        switch ( $type )
        {
            case 'boolean':
            case 'bool':
                $value = self::toBool( $value );
                break;

            case 'string':
                $value = (string)$value;
                break;

            case 'float':
                $value = self::toFloat( $value );
                break;

            case 'int':
                $value = self::toInt( $value );
                break;

            default:
                break;
        }
        $object->$propertyName = $value;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private static function toBool( $value )
    {

        $result = is_string( $value )
            ? Boolean::fromString( $value )
            : (bool)$value;

        return $result;
    }

    /**
     * @param $value
     *
     * @return float
     */
    private static function toFloat( $value )
    {
        $result = is_string( $value )
            ? floatval( $value )
            : (float)$value;

        return $result;
    }

    /**
     * @param $value
     *
     * @return int
     */
    private static function toInt( $value )
    {
        $result = is_string( $value )
            ? intval( $value )
            : (int)$value;

        return $result;
    }

}
