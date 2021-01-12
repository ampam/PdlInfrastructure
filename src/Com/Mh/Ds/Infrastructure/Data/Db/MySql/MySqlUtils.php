<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 6/25/2015
 * Time: 4:41 AM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\MySql;


use Com\Mh\Ds\Infrastructure\Data\Row;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class MySqlUtils
 * @package Com\Mh\Ds\Infrastructure\Data\Db\MySql
 */
class MySqlUtils
{


    const NumericDataTypes = [
        'INTEGER' => true,
        'SMALLINT' => true,
        'INT' => true,
        'MEDIUMINT' => true,
        'BIGINT' => true,
        'DECIMAL' => true,
        'NUMERIC' => true,
        'FLOAT' => true,
        'REAL' => true,
        'DOUBLE'  => true
    ];

    const IntegerDataTypes = [
        'INTEGER'  => true,
        'SMALLINT' => true,
        'INT' => true,
        'MEDIUMINT' => true,
        'BIGINT' => true
    ];

    /**
     * @var array
     */
    private static $config;

    /**
     * @param $config
     */
    public static function setConfig( &$config )
    {
        self::$config = $config;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function isValidTime( $value )
    {
        $result = false;

        $timeParts = explode( ':', $value );


        if ( count( $timeParts ) == 3 )
        {
            $hours = intval( $timeParts[ 0 ], 10 );
            $mins = intval( $timeParts[ 1 ], 10 );
            $second = intval( $timeParts[ 2 ], 10 );

            $result = ( $hours >= 0 && $hours <= 23 ) && ( $mins >= 0 && $mins <= 59 ) && ( $second >= 0 && $second <= 59 );
        }

        return $result;
    }

    /**
     * @param $dataType
     *
     * @return bool
     */
    public static function isIntegerDataType( $dataType )
    {
        $result = array_key_exists( $dataType, self::IntegerDataTypes );
        return $result;
    }

    /**
     * @param $dataType
     *
     * @return bool
     */
    public static function isNumericDataType( $dataType )
    {
        $result = array_key_exists( $dataType, self::NumericDataTypes );
        return $result;
    }

    /**
     * @param Row[] $rows
     *
     * @throws Exception
     */
    public static function convertColumnValuesToType( $rows )
    {
        foreach ( $rows as $row )
        {
            $tableInfo = TableInfo::getTableInfo( $row->getFullTableName() );
            foreach ( $tableInfo->columnInfos as $columnInfo )
            {
                $mainType = $columnInfo->getMainType();
                if ( self::isNumericDataType( $mainType ) )
                {
                    $value = $row->getDbColumnValue( $columnInfo->name );
                    if ( $value !== null && is_string( $value ) )
                    {
                        if ( self::isIntegerDataType( $mainType ) )
                        {
                            $row->setColumnValue( $columnInfo->name, intval( $value ) );
                        }
                        else
                        {
                            $row->setColumnValue( $columnInfo->name, floatval( $value ) );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $value
     * @param bool|true $doDate
     * @param bool|true $doTime
     *
     * @return string
     */
    public static function mySql2DateTime( $value, $doDate = true, $doTime = true )
    {

        $result = '';
        if ( empty( $value ) )
        {
            $value = '1980-01-01 01:01:01';
        }

        $parts = explode( ' ', $value );

        if ( $doDate )
        {
            [
                $year,
                $month,
                $day
            ] = explode( '-', $parts[ 0 ] );
            $result = sprintf( "%02d/%02d/%04d", $month, $day, $year );
        }

        if ( $doTime )
        {
            if ( $result != '' )
            {
                $result .= ' ';
            }

            [
                $hours,
                $mins,
                $seconds
            ] = explode( ':', isset( $parts[ 1 ] )
                ? $parts[ 1 ]
                : ( strpos( $parts[ 0 ], ':' ) !== false
                    ? $parts[ 0 ]
                    : '00:00:00' ) );

            $hours = intval( $hours );

            $ampm = $hours >= 12
                ? 'pm'
                : 'am';
            $hours = $hours % 12;
            if ( $hours == 0 )
            {
                $hours = 12;
            }

            $result .= sprintf( "%02d:%02d:%02d %s", $hours, $mins, $seconds, $ampm );
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @param bool|true $doDate
     * @param bool|true $doTime
     *
     * @return int
     */
    public static function mysql2UnixTime( $value, $doDate = true, $doTime = true )
    {
        $dateTime = MySqlUtils::mySql2DateTime( $value, $doDate, $doTime );
        $result = strtotime( $dateTime );

        if ( $doDate && !$doTime )
        {
            $result = strtotime( date( "Y-m-d", $result ) );
        }
        else if ( !$doDate && $doTime )
        {

            $result = strtotime( date( "H:i:s", $result ) ) - $result = strtotime( date( "Y-m-d", $result ) );
        }

        return $result;
    }


    /**
     * @param $value
     * @param bool|true $doDate
     * @param bool|true $doTime
     *
     * @return string
     * @throws Exception
     */
    public static function unixTime2Mysql( $value, $doDate = true, $doTime = true )
    {

        if ( $value <= 60 * 60 * 24 )
        {
            $dateTime = new DateTime( 'now', new DateTimeZone( self::$config[ 'server_timezone' ] ) );
            $dateTime->setTime( 0, intval( floor( $value / 60 ), $value % 60 ) );
            $value = $dateTime->format( 'U' );
        }
        $result = MySqlUtils::dateParts2Mysql( getdate( intval( $value ) ), $doDate, $doTime );
        return $result;
    }

    /**
     * @param string $value
     * @param bool|true $doDate
     * @param bool|true $doTime
     *
     * @return string
     */
    public static function dateTime2Mysql( string $value, $doDate = true, $doTime = true )
    {
        $result = MySqlUtils::dateParts2Mysql( getdate( strtotime( $value ) ), $doDate, $doTime );
        return $result;
    }

    /**
     * @return string
     */
    public static function getMySqlNow()
    {
        $now = time();
        $formattedNow = date( 'm/d/Y h:i:s A', $now );
        $result = MySqlUtils::dateTime2Mysql( $formattedNow );
        return $result;
    }

    /**
     * @param $dt
     * @param bool|true $doDate
     * @param bool|true $doTime
     *
     * @return string
     */
    public static function dateParts2Mysql( $dt, $doDate = true, $doTime = true )
    {
        $result = '';

        if ( $doDate )
        {
            $mySqlDate = sprintf( "%s-%02d-%02d", $dt[ 'year' ], $dt[ 'mon' ], $dt[ 'mday' ] );
            $result = $mySqlDate;
        }

        if ( $doTime )
        {
            $mySqlTime = sprintf( "%02d:%02d:%02d", $dt[ 'hours' ], $dt[ 'minutes' ], $dt[ 'seconds' ] );
            if ( $result !== '' )
            {
                $result .= ' ';
            }
            $result .= $mySqlTime;
        }

        return $result;
    }

    /**
     * @param string $isoTime
     *
     * @return string
     */
    public static function iso8601ToMySqlDatetime( string $isoTime )
    {
        $dateParts = getdate( strtotime( $isoTime ) );
        $result = MySqlUtils::dateParts2Mysql( $dateParts );
        return $result;
    }


    /**
     * @param $dbDataType
     *
     * @return bool
     */
    public static function isBigTextColumns( $dbDataType )
    {
        $result = $dbDataType === 'text' || $dbDataType === 'tinytext' || $dbDataType === 'longtext';
        return $result;
    }

    /**
     * @param $dbDataType
     *
     * @return bool
     */
    public static function isDatetimeColumn( $dbDataType )
    {
        $result = $dbDataType == 'datetime' || $dbDataType == 'timestamp';
        return $result;
    }


    /**
     * @param $dbDataType
     *
     * @return bool
     */
    public static function isDateColumn( $dbDataType )
    {
        $result = $dbDataType == 'date';
        return $result;
    }

    /**
     * @param $dbDataType
     *
     * @return bool
     */
    public static function isTimeColumn( $dbDataType )
    {
        $result = $dbDataType == 'time';
        return $result;
    }

    /**
     * @param $dbDataType
     *
     * @return bool
     */
    public static function isBitsColumn( $dbDataType )
    {
        $result = strpos( $dbDataType, 'bit(' ) === 0;
        return $result;
    }

    /**
     * @return string
     */
    public static function getNowString()
    {
        $now = time();
        $formattedNow = date( 'm/d/Y h:i:s A', $now );
        $result = self::dateTime2Mysql( $formattedNow );
        return $result;
    }


}
