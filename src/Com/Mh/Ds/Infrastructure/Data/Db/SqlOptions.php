<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 7/18/2016
 * Time: 6:40 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db;

use Com\Mh\Ds\Infrastructure\Data\Db\Sql\FieldList;
use Com\Mh\Ds\Infrastructure\Data\Db\Sql\LimitStatement;
use Com\Mh\Ds\Infrastructure\Data\Db\Sql\OrderByStatement;
use Com\Mh\Ds\Infrastructure\Data\Db\Sql\WhereStatement;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;

/**
 * Class OptionsHelper
 * @package Com\Mh\Ds\Infrastructure\Data\Db
 */
class SqlOptions
{

    const Fields = 'fields';
    const Values = 'values';
    const Table = 'table';
    const DbTable = 'dbTable';
    const Where = 'where';
    const Limit = 'limit';
    const Offset = 'offset';
    const RowsPerPage = 'rowsPerPage';
    const Ids = 'ids';
    const Id = 'id';
    const ForceIndex = 'forceIndex';
    const Log = 'log';
    const Order = 'order';
    const Rows = 'rows';

    /**
     * @param array $options
     *
     * @return string
     */
    public static function toSelect( $options )
    {
        $fields = self::processFieldList( $options );
        $where = self::processWhere( $options );
        $orderBy = self::processOrderBy( $options );
        $limit = self::processLimit( $options );
        $table = $options[ self::Table ];
        $forceIndex = self::processForceIndex( $options );

        $result = "SELECT {$fields} FROM {$table} {$forceIndex} {$where}{$orderBy}{$limit}";

        return $result;

    }

    /**
     * @param $options
     *
     * @return string
     */
    public static function processForceIndex( $options )
    {
        $result = isset( $options[ self::ForceIndex ] )
            ? $options[ self::ForceIndex ]
            : '';

        return $result;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public static function toInsert( $options )
    {
        $fields = DbUtils::fields2Insert( $options[ self::Fields ] );
        $table = $options[ self::Table ];
        $result = "INSERT INTO {$table} {$fields}";
        return $result;

    }

    /**
     * @param array $options
     *
     * @return string
     */
    public static function toMultiInsert( $options )
    {
        $fields = self::processFieldList( $options );

        $valueString = implode( ',', $options[ self::Values ] );

        $result = "INSERT INTO {$options['table']} ( {$fields} ) VALUES {$valueString}";

        return $result;


    }

    /**
     * @param $options
     *
     * @return string
     */
    public static function toMultiUpdateByIds( $options )
    {
        $updateSet = DbUtils::fields2Update( $options[ self::Fields ] );
        $ids = DbUtils::createSafeINList( $options[ self::Ids ] );

        $table = $options[ self::Table ];
        $primaryKey = DbUtils::getPrimaryKeyField( $table );
        $where = self::processWhere( $options );

        $result = "UPDATE {$table} {$updateSet} WHERE {$primaryKey} IN ( $ids ) {$where}";
        return $result;
    }


    /**
     * @param $options
     *
     * @return string
     */
    public static function processLimit( $options )
    {
        $result = '';

        if ( !empty( $options[ self::Limit ] ) )
        {
            $limit = $options[ self::Limit ];

            $result = $limit instanceof LimitStatement
                ? ' ' . $limit->_toString()
                : " LIMIT {$limit}";
        }
//        else if ( !empty( $options[ self::Offset ] ) )
        else if ( isset( $options[ self::Offset ] ) )
        {
            assert( isset( $options[ self::RowsPerPage ] ) );

            $offset = $options[ self::Offset ];
            $result = " LIMIT {$offset}";

            $rowsPerPage = $options[ self::RowsPerPage ];
            $result .= ", {$rowsPerPage}";
        }


        return $result;
    }


    /**
     * @param $options
     *
     * @return string
     */
    public static function toUpdate( $options )
    {
        $fields = DbUtils::fields2Update( $options[ self::Fields ] );
        $where = self::processWhere( $options );
        $limit = self::processLimit( $options );
        $table = $options[ self::Table ];

        $result = "UPDATE {$table} {$fields}{$where}{$limit}";

        return $result;

    }

    /**
     * @param array $options
     *
     * @return string
     */
    public static function processFieldList( $options )
    {
        if ( !empty( $options[ self::Fields ] ) )
        {
            $fields = $options[ self::Fields ];
            if ( $fields instanceof FieldList )
            {
                $fields = $fields->_getColumns();
            }
            $result = is_array( $fields )
                ? implode( ', ', $fields )
                : $fields;
        }
        else
        {
            $result = '*';
        }

        return $result;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    public static function toDelete( $options )
    {
        $where = self::processWhere( $options );
        $limit = self::processLimit( $options );

        $table = $options[ self::Table ];
        $result = "DELETE FROM {$table} {$where}{$limit}";

        return $result;
    }


    /**
     * @param $options
     *
     * @return string
     */
    public static function processWhere( $options )
    {
        $result = '';
        if ( !empty( $options[ self::Where ] ) )
        {
            $where = $options[ self::Where ];

            if ( is_string( $where ) )
            {
                $result = " {$where}";
            }
            else if ( $where instanceof WhereStatement )
            {
                if ( !$where->isEmpty() )
                {
                    $result = ' ' . $where->_toWhere();
                }
            }
            else
            {
                Debug::logAndThrow( "Invalid Where type in SQL Options" );
            }
        }

        return $result;
    }

    /**
     * @param $options
     *
     * @return string
     */
    public static function processOrderBy( $options )
    {
        $result = '';
        if ( !empty( $options[ self::Order ] ) )
        {
            $orderBy = $options[ self::Order ];
            if ( $orderBy instanceof OrderByStatement )
            {
                $result = ' ' . $orderBy->_toOrderBy();
            }
            else
            {
                $result = " {$orderBy}";
            }

        }

        return $result;
    }

}
