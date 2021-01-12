<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 10/28/2018
 * Time: 6:17 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php;

/**
 * Class Boolean
 * @package Com\Mh\Ds\Infrastructure\Languages\Php
 */
class Boolean
{
    const True = true;
    const TrueString = 'true';
    const Yes = 'yes';
    const ShortYes = 'y';
    const On = 'on';
    const One = 1;
    const OneString = '1';

    const TrueValues = [
        self::True,
        self::TrueString,
        self::Yes,
        self::ShortYes,
        self::On,
        self::One,
        self::OneString
    ];

    const TrueStrings = [
        self::TrueString,
        self::Yes,
        self::ShortYes,
        self::On,
        self::OneString
    ];

    const False = false;
    const FalseString = 'false';
    const No = 'no';
    const ShortNo = 'n';
    const Off = 'off';
    const Zero = 0;
    const ZeroString = '0';

    const FalseValues = [
        self::False,
        self::FalseString,
        self::No,
        self::ShortNo,
        self::Off,
        self::Zero,
        self::ZeroString
    ];

    const FalseStrings = [
        self::FalseString,
        self::No,
        self::ShortNo,
        self::Off,
        self::ZeroString
    ];

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isTrueString( $value )
    {
        $result = in_array( $value, Boolean::TrueStrings );
        return $result;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function fromString( $value )
    {
        $result = self::isTrueString( $value );
        return $result;
    }

}
