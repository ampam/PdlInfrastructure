<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 2/28/2017
 * Time: 11:35 AM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\MySql;

use Exception;
use Com\Mh\Ds\Infrastructure\Data\Db\DbUtils;

/**
 * Class TableInfo
 * @property ColumnInfo[] $columnInfos
 * @property ColumnInfo $primaryKeyColumnInfo
 * @property string $tableName
 * @property string $dbName
 * @property string $shortTableName
 * @package Com\Mh\Ds\Infrastructure\Data\Db\MySql
 */
class TableInfo
{
    /** @var  TableInfo[] */
    private static $instances;

    /** @var ColumnInfo[] */
    private $columnsByName;

    /**
     * TableInfo constructor.
     *
     * @param string $tableName
     *
     * @throws Exception
     */
    protected function __construct( $tableName )
    {
        $this->tableName = $tableName;
        $parts = explode( '.', $tableName );
        if ( count( $parts ) == 2 )
        {
            $this->dbName = $parts[ 0 ];
            $this->shortTableName = $parts[ 1 ];
        }
        else
        {
            $this->dbName = '';
            $this->shortTableName = $tableName;
        }
        $this->initialize();
    }

    /**
     * @param string $tableName
     * @param string[] $columns
     *
     * @return string[]
     * @throws Exception
     */
    public static function getRemainingColumns( $tableName, $columns )
    {
        $result = [];

        $tableInfo = self::getTableInfo( $tableName );
        $columnAsKeys = array_flip( $columns );
        foreach( $tableInfo->columnInfos as $columnInfo )
        {
            if ( !key_exists( $columnInfo->field, $columnAsKeys ) )
            {
                $result[] = $columnInfo->field;
            }
        }
        return $result;
    }


    /**
     * @param $tableName
     *
     * @return TableInfo
     * @throws Exception
     */
    public static function getTableInfo( $tableName )
    {
        if ( !isset( self::$instances[ $tableName ]))
        {
            self::$instances[ $tableName ] = new self( $tableName );
        }

        return self::$instances[ $tableName ];
    }

    /**
     * @param $columnName
     *
     * @return ColumnInfo
     */
    public function getColumnInfoByName( $columnName )
    {
        $result = !empty( $this->columnsByName[ $columnName ] )
            ? $this->columnsByName[ $columnName ]
            : null;

        return $result;
    }

    /**
     *
     * @throws Exception
     */
    private function initialize()
    {
        $this->columnInfos = [];

        $rows = DbUtils::getTableColumns( $this->tableName );

        foreach( $rows as $row )
        {
            $columnInfo = new ColumnInfo( $row );
            if ( $columnInfo->isPrimaryKey() )
            {
                $this->primaryKeyColumnInfo = $columnInfo;
            }
            $this->columnInfos[] = $columnInfo;
            $this->columnsByName[ $columnInfo->name ] = $columnInfo;
        }
    }
}
