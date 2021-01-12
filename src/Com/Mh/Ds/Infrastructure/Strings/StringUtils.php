<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 10/29/2015
 * Time: 12:23 AM
 */

namespace Com\Mh\Ds\Infrastructure\Strings;

/**
 * Class StringUtils
 * @package Com\Mh\Ds\Infrastructure
 */
class StringUtils
{
    /**
     * There are certain situations where this method may not work as expected
     *  eg: myWord20 is converted to my_word20 instead of my_word_20
     *
     * @param $value
     *
     * @return string
     */
    public static function camel2SnakeCase( $value )
    {
        $result = strtolower(
            preg_replace(
                [
                    "/([A-Z]+)/",
                    "/_([A-Z]+)([A-Z][a-z])/"
                ],
                [
                    "_$1",
                    "_$1_$2"
                ],
                lcfirst( $value )
            )
        );

        return $result;
    }

    /**
     * Similar to camel2SnakeCase2 but will stop at numbers
     *
     * @param string $value
     * @param $hasDigit
     * @param $hasUppercase
     *
     * @return string
     */
    public static function camel2SnakeCase3( $value, &$hasDigit, &$hasUppercase )
    {
        $parts = [];
        $word = '';
        $length = strlen( $value );
        $prevChar = '';
        $hasDigit = false;
        $hasUppercase = false;

        for ( $i = 0; $i <= $length; $i++ )
        {
            $char = substr( $value, $i, 1 );
            $isDigit = ctype_digit( $char );
            $isUppercase = ctype_upper( $char );

            $isBoundary = $isUppercase || ( $isDigit && !ctype_digit( $prevChar ) );

            if ( $isBoundary && !empty( $word ) )
            {
                $parts[] = $word;
                $word = '';
            }

            $word .= $char;
            $prevChar = $char;

            $hasDigit = $hasDigit || $isDigit;
            $hasUppercase = $hasUppercase || $isUppercase;
        }

        if ( !empty( $word ) )
        {
            $parts[] = $word;
        }

        $result = strtolower( implode( '_', $parts ) );

        return $result;
    }

    /**
     *
     * @param string $value
     *
     * @return string
     */
    public static function camel2SnakeCase2( $value )
    {
        $parts = [];
        $word = '';
        $length = strlen( $value );
        $prevChar = '';

        for ( $i = 0; $i <= $length; $i++ )
        {
            $char = substr( $value, $i, 1 );
            $isDigit = ctype_digit( $char );
            $isPrevDigit = ctype_digit( $prevChar );
            $isUppercase = ctype_upper( $char );

            $isBoundary = $isUppercase || ( $isDigit && !$isPrevDigit );

            if ( $isBoundary && !empty( $word ) )
            {
                $parts[] = $word;
                $word = '';
            }

            $word .= $char;
            $prevChar = $char;
        }

        if ( !empty( $word ) )
        {
            $parts[] = $word;
        }

        $result = strtolower( implode( '_', $parts ) );

        return $result;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function snake2CamelCase( $value )
    {
        $result = lcfirst( self::snake2PascalCase( $value ) );
        return $result;
    }


    /**
     * @param string $str
     *
     * @return string
     */
    public static function trimQuotes( $str )
    {
        return trim( $str, " \t\n\r\0\x0B'" );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function snakeCase2Words( $value )
    {
        $result = str_replace( [
            '_',
            '-'
        ], ' ', $value );
        return $result;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function camelCase2Words( $value )
    {
        $result = self::snakeCase2Words( StringUtils::camel2SnakeCase( $value ) );
        return $result;
    }

    /**
     * @param $value
     *
     * @param $delimiter
     *
     * @return mixed
     */
    public static function delimited2PascalCase( $value, $delimiter )
    {
        $result = str_replace( ' ', '', ucwords( str_replace( $delimiter, ' ', $value ) ) );
        return $result;
    }


    /**
     * @param $value
     *
     * @return string
     */
    public static function snake2PascalCase( $value )
    {
        $result = self::delimited2PascalCase( $value, '_' );
        return $result;
    }

    /**
     * @param $value
     *
     * @return mixed
     * @internal param $name
     *
     */
    public static function kebab2PascalCase( $value )
    {
        $result = self::delimited2PascalCase( $value, '-' );
        return $result;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function kebab2CamelCase( $value )
    {
        $result = lcfirst( self::kebab2PascalCase( $value ) );
        return $result;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function toWords( $value )
    {
        $result = str_replace( [
            '.',
            '-',
            '_'
        ], ' ', $value );
        return $result;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function toCamelCase( $value )
    {
        $result = lcfirst( self::toPascalCase( $value ) );
        return $result;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function toPascalCase( $value )
    {
        $result = str_replace( ' ', '', ucwords( self::toWords( $value ) ) );
        return $result;
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function startsWith( $haystack, $needle )
    {
        $length = strlen( $needle );
        return ( substr( $haystack, 0, $length ) === $needle );
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function endsWith( $haystack, $needle )
    {
        $length = strlen( $needle );

        return $length === 0 || ( substr( $haystack, -$length ) === $needle );
    }

    /**
     * @param $length
     *
     * @return false|string
     */
    public static function generateRandomString( $length )
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = substr( str_shuffle( str_repeat( $alphabet, ceil( $length / strlen( $alphabet ) ) ) ), 1, $length );
        return $result;
    }

    public static function cleanString( $text )
    {
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-',
            // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ',
            // Literally a single quote
            '/[“”«»„]/u' => ' ',
            // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        ];

        $result = preg_replace( array_keys( $utf8 ), array_values( $utf8 ), $text );
        return $result;
    }

}
