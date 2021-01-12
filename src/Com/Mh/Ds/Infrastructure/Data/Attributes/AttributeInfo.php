<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 3/12/2016
 * Time: 6:59 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Attributes;


/**
 * Class AttributeInfo
 * @property  string $name
 * @property array $values
 * @property  array $names
 * @package Com\Mh\Ds\Infrastructure\Data\Attributes
 */
class AttributeInfo
{
    /**
     * AttributeInfo constructor.
     */
    public function __construct()
    {
        $this->values = [];
        $this->names = [];
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return AttributeInfo
     *
     */
    public static function parse( $name, $arguments )
    {
        $result = new AttributeInfo();
        $result->name = $name;
        $result->parseArguments( $arguments );
        return $result;
    }

    /**
     * @param $arguments
     */
    private function parseArguments( $arguments )
    {
        $this->values = array_values( $arguments );
        $this->names = array_keys( $arguments );
    }

    /**
     * @param $indexOrName
     *
     * @return mixed
     */
    public function getValue( $indexOrName )
    {
        $result = is_int( $indexOrName )
            ? $this->values[ $indexOrName ]
            : $this->getValue( array_search( $indexOrName, $this->names ) );

        return $result;
    }

    /**
     * @param $index
     *
     * @return mixed
     */
    public function getName( $index )
    {
        return $this->names[ $index ];
    }

}
