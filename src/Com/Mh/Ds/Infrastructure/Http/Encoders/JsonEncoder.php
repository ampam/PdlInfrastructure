<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/7/2017
 * Time: 2:23 PM
 */

namespace Com\Mh\Ds\Infrastructure\Http\Encoders;

use Com\Mh\Ds\Infrastructure\Http\Inputs;

/**
 * Class JsonEncoder
 * @package Com\Mh\Ds\Infrastructure\Http\Encoders
 */
class JsonEncoder
{
    const DefaultEncoder = 1;
    const PdlEncoder = 2;

    const Param = 'supportedEncoding';
    const ShortParam = 'se';
    const TypeEncoder = 'pdl';

    private static $encoder = self::DefaultEncoder;

    /**
     * @param $object
     *
     * @return string
     */
    public static function encode( $object )
    {
        $result = self::getEncoder( self::$encoder )->encode( $object );
        return $result;
    }

    /**
     *
     */
    public static function determineEncoder()
    {
        $result = Inputs::getString( self::Param ) == self::TypeEncoder || Inputs::exist( self::ShortParam )
            ? self::PdlEncoder
            : self::DefaultEncoder;

        self::setEncoder( $result );
    }

    /**
     * @param int $encoder
     */
    public static function setEncoder( $encoder )
    {
        self::$encoder = $encoder;
    }

    /**
     * @param int $encoderType
     *
     * @return IJsonEncoder
     */
    private static function getEncoder( $encoderType = self::DefaultEncoder )
    {
        $result = null;
        switch( $encoderType )
        {
            case self::PdlEncoder:
                $result = PdlJsonEncoder::getInstance();
                break;

            case self::DefaultEncoder:
            default:
                $result = StandardJsonEncoder::getInstance();
                break;
        }

        return $result;
    }
}
