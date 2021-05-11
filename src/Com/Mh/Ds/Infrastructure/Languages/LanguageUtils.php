<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/7/2017
 * Time: 2:42 AM
 */

namespace Com\Mh\Ds\Infrastructure\Languages;

/**
 * Class LanguageUtils
 * @package Com\Mh\Ds\Infrastructure\Languages
 */
class LanguageUtils
{


    private static $projectNamespaces = [];

    /**
     * @param $config
     */
    public static function setConfig( &$config )
    {
        self::$projectNamespaces =& $config['pdl']['projectNamespaces'];
    }

    /**
     * @param string $phpClassname
     *
     * @return string
     */
    public static function php2PdlClassname( string $phpClassname )
    {
        if ( strpos( $phpClassname, '\\' ) === 0 )
        {
            $phpClassname = substr( $phpClassname, 1 );
        }


        $parts = explode( '\\', $phpClassname );
        $className = array_pop( $parts );
        $pdlNamespace = strtolower( implode( '.', $parts ) );
        $result = "{$pdlNamespace}.{$className}";
        return $result;
    }

    /**
     * @param string $pdlClassName
     *
     * @return bool
     */
    public static function isProjectClass( string $pdlClassName )
    {
        $result = false;
        foreach( self::$projectNamespaces as $projectNamespace )
        {
            $result = strpos( $pdlClassName, $projectNamespace ) === 0;
            if ( $result )
            {
                break;
            }
        }
        return $result;
    }
}
