<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 3/12/2016
 * Time: 10:22 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Attributes;


/**
 * Class Attributable
 * @package Com\Mh\Ds\Infrastructure\Data\Attributes
 */
class Attributable
{
    protected $_propertyAttributes = [];

    /**
     * Attributable constructor
     */
    public function __construct()
    {

    }

    /**
     * @param array $propertyAttributes
     * @param $propertyName
     * @param $attributeName
     *
     * @return AttributeInfo
     */
    public static function getPropertyAttribute( array $propertyAttributes, $propertyName, $attributeName )
    {
        $result = null;

        if ( isset( $propertyAttributes[ $propertyName ] ) )
        {
            $attributes =& $propertyAttributes[ $propertyName ];

            if ( !empty( $attributes ) && isset( $attributes[ $attributeName ] ) )
            {
                $arguments =& $attributes[ $attributeName ];
                if ( !isset( $arguments[ '_info' ] ) )
                {
                    $arguments[ '_info' ] = AttributeInfo::parse( $attributeName, $arguments );
                }

                $result = $arguments[ '_info' ];
            }
        }

        return $result;
    }

    /**
     * @param $attributes
     * @param $propertyName
     * @param $attributeName
     * @param $paramIndex
     *
     * @return string
     */
    public static function getPropertyAttributeParam( $attributes, $propertyName, $attributeName, $paramIndex )
    {
        $attributeInfo = self::getPropertyAttribute( $attributes, $propertyName, $attributeName );

        $result = $attributeInfo != null
            ? $attributeInfo->getValue( $paramIndex )
            : '';

        return $result;
    }

    /**
     * @param $propertyName
     *
     * @param $attributeName
     * @param $paramIndex
     *
     * @return string
     */
    protected function getAttributeParam( $propertyName, $attributeName, $paramIndex )
    {
        $result = self::getPropertyAttributeParam( $this->_propertyAttributes, $propertyName, $attributeName, $paramIndex );
        return $result;
    }

    /**
     * @param $attributeName
     *
     * @return string
     */
    protected function getFirstPropertyByAttribute( $attributeName )
    {
        $result = '';
        foreach( $this->_propertyAttributes as $property => &$attributes )
        {
            if ( isset( $attributes[ $attributeName ] ) )
            {
                $result = $property;
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $attributeName
     *
     * @return array
     */
    protected function getPropertiesByAttribute( $attributeName )
    {
        $result = [];
        foreach( $this->_propertyAttributes as $property => &$attributes )
        {
            if ( isset( $attributes[ $attributeName ] ) )
            {
                $result[] = $property;
            }
        }

        return $result;
    }




}
