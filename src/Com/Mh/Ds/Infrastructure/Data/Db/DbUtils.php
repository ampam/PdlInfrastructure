<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 6/25/2015
 * Time: 4:03 AM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db;


use Com\Mh\Ds\Infrastructure\Cache\Cache;
use Com\Mh\Ds\Infrastructure\Data\Db\MySql\DbConnectionImpl;
use Com\Mh\Ds\Infrastructure\Data\Row;
use Exception;
use Com\Mh\Ds\Infrastructure\Data\Db\MySql\TableInfo;
use Com\Mh\Ds\Infrastructure\Data\Db\Sql\WhereStatement;


/**
 * Class DbUtils
 * @package Com\Mh\Ds\Infrastructure\Data\Db
 */
class DbUtils
{
    const ShowColumnKey = 'table-columns-%s';

    /** @var IDbConnection */
    private static $dbConnection = null;

    /**
     * @var array
     */
    private static $config;

    /**
     * @var callable
     */
    protected static $escapeFunc = null;

    /**
     * @param $config
     *
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection*/
    public static function setConfig( &$config )
    {
        self::$config = $config;
    }

    /**
     *
     * @return IDbConnection
     */
    private static function connect()
    {

        $result = self::getConnectionInstance();

        $result->enableQueryLog( self::$config[ 'debug' ][ 'logQueriesEnabled' ] );
        $result->connect( self::$config[ 'mysql' ] );

        self::$dbConnection = $result;

        self::$escapeFunc = function( $value ):string  {
            $result = self::$dbConnection->escapeString( $value );
            return $result;
        };

        return $result;
    }


    /**
     * @param $string
     *
     * @return string
     */
    public static function escapeString( $string )
    {
        $escapeFunc = self::$escapeFunc;
        $result = $escapeFunc( $string );
        return $result;
    }

    /**
     * @return IDbConnection
     */
    public static function getDbConnection()
    {
        if ( null === self::$dbConnection )
        {
            self::connect();
        }
        return self::$dbConnection;
    }

    /**
     * @param $tableName
     *
     * @return array
     * @throws Exception
     */
    public static function getTableColumns( $tableName )
    {
        $cacheKey = sprintf( self::ShowColumnKey, $tableName );
        $result = Cache::get( $cacheKey );
        if ( empty( $result ) )
        {
            $result = self::$dbConnection->getAllRows( "SHOW COLUMNS FROM {$tableName}" );
            Cache::set( $cacheKey, $result );
        }
        return $result;
    }

    /**
     * @param $fields
     *
     * @return string
     */
    public static function fields2Update( $fields )
    {
        $result = DbUtils::fields2UpdateWithOperators( $fields );
        return $result;

    }

    /**
     * @param $elements
     *
     * @return string
     */
    public static function createSafeINList( $elements )
    {
        $result = [];
        foreach ( $elements as $element )
        {
            $result[] = '"' . self::escapeString( $element ) . '"';
        }

        return implode( ',', $result );
    }



    /**
     * @param $fields
     *
     * @return string
     */
    public static function fields2UpdateWithOperators( $fields )
    {
        $result = '';

        foreach ( $fields as $field => $rightSide )
        {
            if ( !empty( $result ) )
            {
                $result .= ', ';
            }

            $field = "`{$field}`";

            if ( is_array( $rightSide ) )
            {
                $operation = $rightSide[ 'oper' ];
                $value = $rightSide[ 'value' ];
            }
            else
            {
                $operation = '';
                $value = $rightSide;
            }

            $escapedValue = addslashes( $value );

            switch ( $operation )
            {
                case RowOperation::Add:
                    {
                        $result .= "{$field} = {$field} + {$value}";
                        break;
                    }
                case RowOperation::Sub:
                    {
                        $result .= "{$field} = {$field} - {$value}";
                        break;
                    }
                case RowOperation::Mul:
                    {
                        $result .= "{$field} = {$field} * {$value}";
                        break;
                    }
                case RowOperation::Div:
                    {
                        $result .= "{$field} = {$field} / {$value}";
                        break;
                    }
                case RowOperation::Concat:
                    {
                        $result .= "{$field} = CONCAT_WS('', {$field}, '{$escapedValue}')";
                        break;
                    }
                default:
                    {
                        //plain assign
                        $result .= "{$field} = '{$escapedValue}'";
                    }
            }
        }

        $result = " SET {$result}";

        return $result;
    }

    /**
     * @param array|Row $fields
     * @param bool|false $escapeWithMySql
     *
     * @return string
     */
    public static function fields2Insert( $fields, $escapeWithMySql = false )
    {
        if ( $fields instanceof Row )
        {
            $fields = $fields->toDbRow( [] );
        }

        foreach ( $fields as $field => $value )
        {
            $fields[ $field ] = self::escape( $value, $escapeWithMySql );
        }

        $fieldList = '`' . implode( '`,`', array_keys( $fields ) ) . '`';
        $values = "'" . implode( "','", array_values( $fields ) ) . "'";

        $result = " ($fieldList) VALUES ({$values})";

        return $result;
    }

    /**
     * @param callable $escapeFunc
     */
    public static function setEscapeFunction( callable $escapeFunc )
    {
        self::$escapeFunc = $escapeFunc;
    }

    /**
     * @param $value
     * @param bool $escapeWithDriver
     *
     * @return string
     */
    public static function escape( $value, $escapeWithDriver = true )
    {
        if ( $escapeWithDriver )
        {
            $escapeFunc = self::$escapeFunc;
            $result = $escapeFunc( $value );
        }
        else
        {
            $result = addslashes( $value );
        }
        return $result;
    }

    /**
     * @param WhereStatement $where
     * @param $singleLineFunction
     *
     * @return array
     */
    public static function where2singleLineFunction( WhereStatement $where, $singleLineFunction )
    {
        $result = [];
        $rows = $where->_loadRows();

        /** @var Row $row */
        foreach ( $rows as $row )
        {
            $result[ $row->getDbId() ] = $singleLineFunction( $row );
        }

        return $result;
    }

    /**
     * @param $table
     *
     * @return mixed
     * @throws Exception
     */
    public static function getPrimaryKeyField( $table )
    {
        $tableInfo = TableInfo::getTableInfo( $table );
        $result = $tableInfo->primaryKeyColumnInfo->name;
        return $result;
    }

    /**
     * @return IDbConnection
     *
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function getConnectionInstance():IDbConnection
    {
        //TODO read from config what class to use
        $result = new DbConnectionImpl();
        return $result;
    }


}
