<?php /** @noinspection PhpUnused */

/**
 * Created by PhpStorm.
 * User: am
 * Date: 6/25/2015
 * Time: 4:57 AM
 */

namespace Com\Mh\Ds\Infrastructure\Diagnostic;

use DateTime;
use Exception;

/**
 * Class Debug
 * @package Com\Mh\Ds\Infrastructure\Diagnostic
 */
class Debug
{
    private static $logFile = null;


    /**
     * @var array
     */
    private static $config = null;

    /**
     * @param $config
     */
    public static function setConfig( &$config )
    {
        self::$config = $config;
    }

    /**
     * @param $logFile
     */
    public static function setLogFile( $logFile )
    {
        self::$logFile = $logFile;
    }

    /**
     * @return null|string
     */
    public static function getLogFile()
    {
        $result = self::$logFile;

        if ( empty( $result ) )
        {
            if ( !empty( self::$config ) )
            {
                $result = self::$config[ 'logFile' ];
            }
            else
            {
                $result = "/hdd1/logs/bizapps.log";
            }
        }

        return $result;

    }

    /**
     * @param $xDebugToken
     */
    public static function checkXDebug( $xDebugToken )
    {
        if ( isset( $_COOKIE[ 'XDEBUG_SESSION' ] ) && $_COOKIE[ 'XDEBUG_SESSION' ] == $xDebugToken ||
            isset( $_REQUEST[ 'XDEBUG_SESSION_START' ] ) && $_REQUEST[ 'XDEBUG_SESSION_START' ] == $xDebugToken )
        {
            set_time_limit( 60 * 60 );
        }
    }

    /**
     *
     */
    public static function logCallStack()
    {
        $e = new Exception();
        self::log( $e->getTraceAsString() );
    }


    /**
     * @param bool $addTime
     */
    public static function logGet( $addTime = true )
    {
        Debug::log( $_GET, $addTime );
    }

    /**
     * @param bool $addTime
     */
    public static function logPost( $addTime = true )
    {
        Debug::log( $_POST, $addTime );
    }

    /**
     * @param bool $addTime
     */
    public static function logServer( $addTime = true )
    {
        Debug::log( $_SERVER, $addTime );
    }


    /**
     * @param bool $addTime
     */
    public static function logReq( $addTime = true )
    {
        Debug::log( $_REQUEST, $addTime );
    }

    /**
     * @param $value
     * @param bool $addTime
     */
    public static function log1( $value, $addTime = true )
    {
        $value = self::getLogString( $value, $addTime, "\t" );
        file_put_contents( self::getLogFile(), "\n\t{$value}", FILE_APPEND );
    }

    /**
     * @param $value
     * @param bool $addTime
     */
    public static function log2( $value, $addTime = true )
    {
        $value = self::getLogString( $value, $addTime, "\t\t" );
        file_put_contents( self::getLogFile(), "\n\t\t{$value}", FILE_APPEND );
    }

    /**
     * @param $value
     * @param bool $addTime
     */
    public static function log3( $value, $addTime = true )
    {
        $value = self::getLogString( $value, $addTime, "\t\t\t" );
        file_put_contents( self::getLogFile(), "\n\t\t\t{$value}", FILE_APPEND );
    }

    /**
     * @param $value
     * @param bool $addTime
     */
    public static function log( $value, $addTime = true )
    {
        $value = self::getLogString( $value, $addTime );

        //echo $value;
        file_put_contents( self::getLogFile(), "\n{$value}", FILE_APPEND );
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    public static function logException( Exception $exception )
    {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $line = $exception->getLine();
        $file = $exception->getFile();
        $result = "Exception! - code:{$code} - message: {$message} - file:{$file} - line: {$line}";
        self::log( $result );

        return $result;

    }

    /**
     * @param $message
     *
     * @throws Exception
     */
    public static function logAndThrow( $message )
    {
        self::log( $message );
        throw new Exception( $message );
    }

    /**
     * @param $result
     * @param $addTime
     *
     * @param string $indent
     *
     * @return mixed|string
     */
    private static function getLogString( $result, $addTime, $indent = '' )
    {
        if ( is_array( $result ) )
        {
            $result = print_r( $result, true );
        }
        else if ( $result instanceof DateTime )
        {
            $result = $result->format( 'r' );
        }
        else if ( is_object( $result ) )
        {
            $result = var_export( $result, true );
        }

        if ( $addTime )
        {
            $result = date( 'l, F j, Y g:i:s A' ) . "-" . $result;
        }
        $result = str_replace( PHP_EOL, PHP_EOL . $indent, $result );

        return $result;
    }
}


