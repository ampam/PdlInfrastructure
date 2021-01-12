<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/28/2015
 * Time: 7:51 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data;


use Com\Mh\Ds\Infrastructure\Cache\Cache;
use Com\Mh\Ds\Infrastructure\Data\Attributes\Attributable;
use Com\Mh\Ds\Infrastructure\Data\Db\IDbOperations;
use Com\Mh\Ds\Infrastructure\Data\Db\SqlOptions;
use Com\Mh\Ds\Infrastructure\Strings\StringUtils;
use Com\Mh\Ds\Infrastructure\Data\Attributes\Attributes;
use Com\Mh\Ds\Infrastructure\Data\Db\DbOperations;
use Com\Mh\Ds\Infrastructure\Data\Db\DbUtils;
use Doctrine\Common\Inflector\Inflector;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class Row
 * @package Com\Mh\Ds\Infrastructure\Data
 */
abstract class Row extends Attributable
{
    const InvalidDbId = -1;

    const DbName = 'unknown';
    const TableName = 'unknown';
    const FullTableName = 'unknown.unknown';
    const DefaultCacheExpiration = 5 * 60;

    private static $columnsCache = [];

    protected $_log = false;

    /** @var IDbOperations */
    protected $_db = null;

    protected $_dbIdColumnName;
    protected $_dbIdProperty;
    protected $_property2ColumnNameMap = [];

    /** @var  IRowFactory */
    private static $rowFactory;

    protected $dateModifiedColumn = '';
    private $_affectedRows = 0;


    /**
     * Row constructor.
     *
     * @param array $arguments
     *
     * @phan-suppress PhanMismatchVariadicParam
     */
    function __construct( ...$arguments )
    {
        parent::__construct();

        $this->_db = count( $arguments ) > 0
            ? $arguments[ 0 ]
            : null;

        $this->init();
        $this->setDbId( self::InvalidDbId );

    }


    /**
     * @return IDbOperations
     */
    private function getDb()
    {
        if ( $this->_db === null )
        {
            $this->_db = DbOperations::getInstance();
        }

        return $this->_db;

    }

    /**
     * @param Row[] $rows
     * @param callable $filterFunc
     * @param bool $byDbId
     *
     * @return Row[]
     */
    public static function filter( array $rows, callable $filterFunc, $byDbId = false )
    {
        $result = [];
        foreach ( $rows as $row )
        {
            if ( $filterFunc( $row ) )
            {
                if ( $byDbId )
                {
                    $result[ $row->getDbId() ] = $row;
                }
                else
                {
                    $result[] = $row;
                }
            }
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->_affectedRows;
    }

    /**
     * @param $rowClass
     *
     * @return string
     */
    public static function getNamespaceFromRowTable( $rowClass )
    {
        $parts = explode( "\\", $rowClass );
        array_pop( $parts );

        $result = implode( "\\", $parts );
        return $result;
    }

    /**
     * @param string $fullTablename
     *
     * @return string
     */
    public static function getColumnClassFromTable( string $fullTablename )
    {
        $tableName = explode( '.', $fullTablename )[ 1 ];
        $result = StringUtils::toPascalCase( Inflector::singularize( $tableName ) ) . "Columns";
        return $result;
    }

    /**
     *
     * @param string $rowClass
     *
     * @return string
     */
    public static function getColumnClassFromRow( $rowClass = '' )
    {
        $fullTableName = static::internalGetTablenameFromClass( $rowClass );
        $namespace = static::getNamespaceFromRowTable( $rowClass );
        $columnClass = static::getColumnClassFromTable( $fullTableName );

        $result = "{$namespace}\\{$columnClass}";
        return $result;
    }


    /**
     * @param string $rowClass
     *
     * @return string
     */
    private static function internalGetTablenameFromClass( $rowClass = '' )
    {
        $result = empty( $rowClass )
            ? static::FullTableName
            : self::getFullTablenameFromClass( $rowClass );

        return $result;
    }

    /**
     * @param string $rowClass
     *
     * @return array
     * @throws ReflectionException
     */
    public static function getColumns( $rowClass = '' )
    {
        $fullTableName = static::internalGetTablenameFromClass( $rowClass );

        if ( !isset( self::$columnsCache[ $fullTableName ] ) )
        {
            $columnClass = self::getColumnClassFromRow( $rowClass );

            $reflectionClass = new ReflectionClass( $columnClass );
            self::$columnsCache[ $fullTableName ] = $reflectionClass->getConstants();
        }
        return self::$columnsCache[ $fullTableName ];
    }

    /**
     * @param $columnName
     * @param string $rowClass
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function hasColumn( $columnName, $rowClass = '' )
    {
        $columns = static::getColumns( $rowClass );
        $result = key_exists( StringUtils::toPascalCase( $columnName ), $columns );
        return $result;
    }

    /**
     * @param String $value
     */
    public function setDateModifiedColumn( string $value )
    {
        $this->dateModifiedColumn = $value;
    }


    /**
     * @param Boolean $value
     */
    public function setLog( bool $value )
    {
        $this->_log = $value;
    }

    /**
     * @param Row[] $rows
     * @param string $columnName
     *
     * @return array
     */
    public static function getColumnAsKey( array $rows, string $columnName )
    {
        $result = self::getColumn( $rows, $columnName, false, true );
        return $result;
    }

    /**
     * @param Row[] $rows
     * @param string $columnName
     *
     * @return array
     */
    public static function getColumnAsUnique( array $rows, string $columnName )
    {
        $result = array_keys( self::getColumnAsKey( $rows, $columnName ) );
        return $result;
    }

    /**
     * @param Row[] $rows
     * @param string $columnName
     * @param bool $byDbId
     * @param bool $asKey
     *
     * @return array
     */
    public static function getColumn( array $rows, string $columnName, $byDbId = false, $asKey = false )
    {
        $result = [];

        if ( $byDbId )
        {
            foreach ( $rows as $row )
            {
                $result[ $row->getDbId() ] = $row->getDbColumnValue( $columnName );
            }
        }
        else if ( $asKey )
        {
            foreach ( $rows as $row )
            {
                $result[ $row->getDbColumnValue( $columnName ) ] = true;
            }
        }
        else
        {
            foreach ( $rows as $row )
            {
                $result[] = $row->getDbColumnValue( $columnName );
            }
        }

        return $result;
    }


    /**
     * @param Row[] $rows
     *
     * @return Row[]
     */
    public static function byDbId( array $rows )
    {
        $result = [];

        foreach ( $rows as $row )
        {
            $result[ $row->getDbId() ] = $row;
        }
        return $result;
    }

    /**
     * @param IRowFactory $rowFactory
     */
    public static function setDefaultFactory( IRowFactory $rowFactory )
    {
        self::$rowFactory = $rowFactory;
    }

    /**
     * @return IRowFactory
     */
    public static function getFactory()
    {
        return self::$rowFactory;
    }


    /**
     * @param array $dbRow
     * @param bool $escapeWithMySql
     *
     * @return string
     *
     */
    protected static function dbRow2InsertValues( array $dbRow, bool $escapeWithMySql )
    {
        $values = [];

        $mysqlDb = DbUtils::getDbConnection();

        foreach ( $dbRow as $columnName => $value )
        {
            if ( $escapeWithMySql )
            {
                $values[] = "'" . $mysqlDb->escapeString( $value ) . "'";
            }
            else
            {
                $values[] = "'" . addslashes( $value ) . "'";
            }
        }

        $result = '(' . implode( ',', $values ) . ')';

        return $result;
    }

    /**
     * @param $dbId
     *
     * @return bool
     */
    public static function isValidDbId( $dbId )
    {
        $result = !empty( $dbId ) && $dbId > 0;
        return $result;
    }

    /**
     * @return bool
     */
    public function hasValidDbId()
    {
        $result = self::isValidDbId( $this->getDbId() );
        return $result;
    }

    /**
     * @return bool
     */
    public function isValidRow()
    {
        return $this->hasValidDbId();
    }


    /**
     * @param $dbId
     *
     * @return string
     */
    private function getDbIdWhere( $dbId )
    {
        $result = "WHERE {$this->_dbIdColumnName} = {$dbId}";
        return $result;
    }


    /**
     * @param $dbId
     * @param null $columnList
     */
    public function load( $dbId, $columnList = null )
    {
        if ( is_numeric( $dbId ) )
        {
            $dbRow = $this->getDb()->selectOne( [
                SqlOptions::Table => static::FullTableName,
                SqlOptions::Fields => $columnList,
                SqlOptions::Where => $this->getDbIdWhere( $dbId ),
                SqlOptions::Log => $this->_log
            ] );
            $this->loadFromDbRow( $dbRow );
        }
    }

    /**
     * @param $selectOptions
     */
    public function loadOne( $selectOptions )
    {
        $selectOptions[ 'log' ] = $this->_log;
        $dbRow = $this->getDb()->selectOne( $selectOptions );
        $this->loadFromDbRow( $dbRow );
    }

    /**
     * @return int
     */
    public function getDbId()
    {
        $dbIdProperty = $this->_dbIdProperty;
        $result = $this->$dbIdProperty;
        return $result;
    }

    /**
     *
     */
    public function createOrUpdate()
    {
        if ( $this->isValidRow() )
        {
            $this->update();
        }
        else
        {
            $this->create();
        }
    }

    /**
     * @return int
     */
    public function create()
    {
        $dbRow = $this->toDbRowForInsert();

        $this->setDateModified( $dbRow );

        $result = $this->getDb()->insert( [
            SqlOptions::Table => static::FullTableName,
            SqlOptions::Fields => $dbRow
        ] );

        $this->setDbId( $result );

        return $result;

    }

    /**
     * @return int
     */
    public function duplicate()
    {
        $result = $this->create();
        return $result;
    }

    /**
     * @return int
     */
    public function update()
    {
        $result = 0;

        $dbRow = $this->toDbRowForUpdate();

        if ( !empty( $dbRow ) )
        {
            $result = $this->internalUpdate( $dbRow, $this->getDbId() );
        }

        return $result;
    }

    /**
     * @param $updateOptions
     */
    public function updateOneWhere( $updateOptions )
    {
        $dbRow = $this->toDbRowForUpdate();
        $this->internalOneUpdateWhere( $dbRow, $updateOptions );
    }

    /**
     * @param $targetDbId
     *
     */
    public function duplicateOn( $targetDbId )
    {
        $dbRow = $this->toDbRowForUpdate();
        $this->internalUpdate( $dbRow, $targetDbId );
        $this->setDbId( $targetDbId );
    }

    /**
     * @param $columnValues
     * @param $dbId
     *
     * @return int
     */
    protected function internalUpdate( &$columnValues, $dbId )
    {
        $this->setDateModified( $columnValues );

        $this->_affectedRows = $this->getDb()->updateOne( [
            SqlOptions::Table => static::FullTableName,
            SqlOptions::Fields => $columnValues,
            SqlOptions::Where => $this->getDbIdWhere( $dbId )
        ] );

        return $this->_affectedRows;
    }

    /**
     * @param $columnValues
     * @param $updateOptions
     */
    protected function internalOneUpdateWhere( &$columnValues, $updateOptions )
    {
        $this->setDateModified( $columnValues );

        $updateOptions[ SqlOptions::Table ] = static::FullTableName;
        $updateOptions[ SqlOptions::Fields ] = $columnValues;

        $this->getDb()->updateOne( $updateOptions );
    }

    /**
     *
     */
    public function delete()
    {
        $dbId = $this->getDbId();
        $this->getDb()->deleteOne( [
            SqlOptions::Table => static::FullTableName,
            SqlOptions::Where => $this->getDbIdWhere( $dbId )
        ] );
    }

    /**
     *
     */
    protected function init()
    {
        $this->determinePrimaryKey();
    }

    /**
     * @return string
     */
    public function getDbIdProperty()
    {
        return $this->_dbIdProperty;
    }

    /**
     * @return string
     */
    public function getDbIdColumnName()
    {
        return $this->_dbIdColumnName;
    }

    /**
     *
     */
    protected function determinePrimaryKey()
    {
        $propertyName = $this->getFirstPropertyByAttribute( Attributes::IsDbId );
        if ( !empty( $propertyName ) )
        {
            $this->_dbIdProperty = $propertyName;
            $this->_dbIdColumnName = $this->getColumnNameFromProperty( $propertyName );
        }
    }

    /**
     * @return string
     */
    public function getFullTableName()
    {
        return static::FullTableName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        $result = explode( '.', static::FullTableName )[ 1 ];
        return $result;
    }

    /**
     * @param $dbRow
     */
    public function loadFromDbRow( $dbRow )
    {
        if ( !empty( $dbRow ) )
        {
            foreach ( $dbRow as $columnName => &$value )
            {
                $this->setColumnValue( $columnName, $value );
            }
        }
        else
        {
            $this->setDbId( Row::InvalidDbId );
        }
    }

    /**
     * @param $properties
     */
    public function loadFromProperties( &$properties )
    {
        if ( !empty( $properties ) )
        {
            foreach ( $properties as $propertyName => &$value )
            {
                $this->$propertyName = $value;
            }
        }
        else
        {
            $this->setDbId( Row::InvalidDbId );
        }
    }


    /**
     * @param Row[] $rows
     * @param string[] $exclusionList
     *
     * @return array
     */
    public static function toDbRows( array $rows, $exclusionList = [] )
    {
        $result = [];
        foreach ( $rows as $row )
        {
            $result[] = $row->toDbRow( $exclusionList );
        }

        return $result;
    }

    /**
     * @param $exclusionList
     *
     * @return array|object
     */
    public function toDbRow( $exclusionList )
    {
        $result = [];
        $refObject = new ReflectionObject( $this );
        $properties = $refObject->getProperties( ReflectionProperty::IS_PUBLIC );

        foreach ( $properties as $property )
        {
            if ( !$property->isStatic() && !in_array( $property->name, $exclusionList ) )
            {
                $columnName = $this->getColumnNameFromProperty( $property->name );
                $result[ $columnName ] = $property->getValue( $this );
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function toDbRowNoIdNoDates()
    {
        $result = $this->toDbRow( [
            $this->_dbIdProperty,
            'dateAdded',
            'dateModified'
        ] );
        return $result;
    }

    /**
     * @return array
     */
    protected function toDbRowForInsert()
    {
        $result = $this->toDbRowNoIdNoDates();

        return $result;
    }

    /**
     * @return array
     */
    protected function toDbRowNoId()
    {
        $result = $this->toDbRow( [ $this->_dbIdProperty ] );
        return $result;
    }

    /**
     * @param $dbId
     *
     */
    public function setDbId( $dbId )
    {
        $dbIdProperty = $this->_dbIdProperty;
        $this->$dbIdProperty = $dbId;
    }

    /**
     * @param $columnName
     *
     * @return mixed
     */
    public function getDbColumnValue( $columnName )
    {
        $property = self::column2PropertyName( $columnName );

        $result = property_exists( $this, $property )
            ? $this->$property
            : null;

        return $result;
    }

    /**
     * @param string $expression
     * @param array|null $columnList
     *
     * @return bool
     */
    public function loadWhere( string $expression, $columnList = null )
    {

        $dbRow = $this->getDb()->selectOne( [
            SqlOptions::Table => static::FullTableName,
            SqlOptions::Fields => $columnList,
            SqlOptions::Where => "WHERE {$expression}",
            SqlOptions::Log => $this->_log
        ] );


        $this->loadFromDbRow( $dbRow );

        return $this->hasValidDbId();

    }

    /**
     * @param $dbRow
     *
     * @return int
     */
    public function updateColumns( $dbRow )
    {
        $columnValues = [];
        $columnValues = $this->setDateModified( $columnValues );

        foreach ( $dbRow as $columnName )
        {
            $columnValues[ $columnName ] = $this->getDbColumnValue( $columnName );
        }

        $result = $this->internalUpdate( $columnValues, $this->getDbId() );

        return $result;
    }


    /**
     * @param $dbRows
     * @param $rowClass
     *
     * @return Row[]
     */
    public static function dbRowsToRows( &$dbRows, $rowClass )
    {
        $result = [];
        $rowFactory = self::getFactory();
        foreach ( $dbRows as &$dbRow )
        {
            $row = $rowFactory->create( $rowClass );
            $row->loadFromDbRow( $dbRow );
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @param $updateOptions
     * @param string $tableName
     */
    public static function multiUpdate( $updateOptions, $tableName = '' )
    {
        if ( empty( $tableName ) )
        {
            $tableName = static::FullTableName;
        }

        $row = self::createInstance();
        $updateOptions[ SqlOptions::Table ] = $tableName;
        $row->getDb()->update( $updateOptions );
    }


    /**
     * @param Row[] $rows
     *
     * $rows should belong to the same table
     * and most contain the same columns
     *
     * return array of inserted new ids
     *
     * @return array
     */
    public static function multiInsert( array &$rows )
    {
        $result = [];

        if ( !empty( $rows ) )
        {

            $values = [];

            foreach ( $rows as &$row )
            {
                $dbRow = $row->toDbRowForInsert();
                $row->setDateModified( $dbRow );

                $values[] = self::dbRow2InsertValues( $dbRow, true );
            }

            $firstRow = $rows[ 0 ];
            $firstDbRow = $firstRow->toDbRowNoId();

            $db = $firstRow->getDb();

            $result = $db->multiInsert( [
                SqlOptions::Table => $firstRow->getFullTableName(),
                SqlOptions::Fields => array_keys( $firstDbRow ),
                SqlOptions::Values => $values
            ] );

            for ( $i = 0; $i < count( $result ); $i++ )
            {
                $rows[ $i ]->setDbId( $result[ $i ] );
            }

        }

        return $result;
    }

    /**
     * @return array
     */
    protected function toDbRowForUpdate()
    {
        $result = $this->toDbRowNoIdNoDates();
        return $result;
    }

    /**
     * @param $columnName
     * @param $value
     */
    public function setColumnValue( $columnName, $value )
    {
        $propertyName = self::column2PropertyName( $columnName );
        $this->$propertyName = $value;
    }


    /**
     * @param array $columnValues
     */
    public function setColumnValues( array $columnValues )
    {
        foreach ( $columnValues as $columnName => $value )
        {
            $this->setColumnValue( $columnName, $value );
        }
    }


    /**
     * @param string $columnName
     *
     * @return string
     */
    public static function column2PropertyName( string $columnName )
    {
        $result = StringUtils::snake2CamelCase( $columnName );
        return $result;
    }

    /**
     * @param $propertyName
     *
     * @return string
     */
    private function getColumnNameFromProperty( $propertyName )
    {
        if ( !isset( $this->_property2ColumnNameMap[ $propertyName ] ) )
        {
            $result = $this->getAttributeParam( $propertyName, Attributes::ColumnName, 0 );
            if ( empty( $result ) )
            {
                $result = StringUtils::camel2SnakeCase2( $propertyName );
            }

            $this->_property2ColumnNameMap[ $propertyName ] = $result;
        }
        else
        {
            $result = $this->_property2ColumnNameMap[ $propertyName ];
        }

        return $result;
    }

    /**
     * @param array $selectOptions
     *
     * @param string $rowClass
     *
     * @return Row
     */
    public static function loadGenericRow( array $selectOptions, string $rowClass )
    {
        $result = self::getFactory()->create( $rowClass );
        $result->loadOne( $selectOptions );
        return $result;
    }

    /**
     *
     * @param array $selectOptions
     *
     * @param string $rowClass
     * @param bool $byDbId
     *
     * @return self[]
     */
    public static function loadGenericRows( array $selectOptions, string $rowClass, $byDbId = false )
    {
        $result = [];

        $dbRows = self::$rowFactory->getDb()->select( $selectOptions );

        $rowFactory = Row::getFactory();

        foreach ( $dbRows as $dbRow )
        {
            $row = $rowFactory->create( $rowClass );
            $row->loadFromDbRow( $dbRow );
            if ( $byDbId )
            {
                $result[ $row->getDbId() ] = $row;
            }
            else
            {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     *
     *
     * @param string $rowClass
     *
     * @return static
     */
    public static function createInstance( $rowClass = '' )
    {
        if ( empty( $rowClass ) )
        {
            $rowClass = static::class;
        }
        $result = self::getFactory()->create( $rowClass );
        return $result;
    }

    /**
     * @param $rowClass
     *
     * @return string
     */
    public static function getFullTablenameFromClass( $rowClass )
    {
        $rowFactory = Row::getFactory();
        $result = $rowFactory->create( $rowClass )->getFullTableName();
        return $result;
    }

    /**
     *
     * @param array $selectOptions
     * @param bool $byDbId
     *
     * @param string $rowClass
     *
     * @return Row[]
     */
    public static function loadRows( array $selectOptions, $byDbId = false, $rowClass = '' )
    {
        $rowFactory = Row::getFactory();

        if ( empty( $rowClass ) )
        {
            $rowClass = static::class;
            $fullTableName = static::FullTableName;
            assert( static::class !== Row::class, "Invalid Call to loadRows " );
        }
        else
        {
            $fullTableName = self::getFullTablenameFromClass( $rowClass );
        }

        $result = [];
        $dbRows = self::loadDbRows( $selectOptions, $fullTableName );

        foreach ( $dbRows as $dbRow )
        {
            $row = $rowFactory->create( $rowClass );
            $row->loadFromDbRow( $dbRow );
            if ( $byDbId )
            {
                $result[ $row->getDbId() ] = $row;
            }
            else
            {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     *
     * @param array $selectOptions
     *
     * @param string $tableName
     *
     * @return array[]
     */
    public static function loadDbRows( array $selectOptions, $tableName = '' )
    {
        if ( empty( $tableName ) )
        {
            $tableName = static::FullTableName;
            assert( static::class !== Row::class, "Invalid call to loadDbRows" );
        }
        $selectOptions[ SqlOptions::Table ] = $tableName;
        $result = self::getFactory()->getDb()->select( $selectOptions );

        return $result;
    }

    /**
     * @param WhereStatement|String $where
     * @param null $columnList
     * @param string $rowClass
     *
     * @return static
     *
     * @throws Exception
     */
    public static function loadRowWhere( $where, $columnList = null, $rowClass = '' )
    {
        if ( empty( $rowClass ) )
        {
            $rowClass = static::class;
        }

        /** @var static $result */
        $result = self::createInstance( $rowClass );

        $whereExpression = $where instanceof WhereStatement
            ? $where->_toString()
            : $where;

        $result->loadWhere( $whereExpression, $columnList );

        return $result;
    }

    /**
     * @param array $selectOptions
     *
     * @return array
     */
    public static function loadDbRow( array $selectOptions )
    {
        $selectOptions[ SqlOptions::Table ] = static::FullTableName;
        $result = self::getFactory()->getDb()->selectOne( $selectOptions );
        return $result;
    }

    /**
     * @param $dbId
     * @param null $columnList
     *
     * @param string $rowClass
     *
     * @return static
     */
    public static function loadRow( $dbId, $columnList = null, $rowClass = '' )
    {
        if ( empty( $rowClass ) )
        {
            $rowClass = static::class;
            assert( static::class !== Row::class, "Invalid Call to loadRow" );
        }

        /** @var static $result */
        $result = self::createInstance( $rowClass );

        $result->load( $dbId, $columnList );

        return $result;
    }

    /**
     * @param array $deleteOptions
     *
     * @param string $tableName
     *
     * @return int
     */
    public static function deleteWhere( array $deleteOptions, $tableName = '' )
    {
        if ( empty( $tableName ) )
        {
            $tableName = static::FullTableName;
        }
        $deleteOptions[ SqlOptions::Table ] = $tableName;
        $result = self::getFactory()->getDb()->delete( $deleteOptions );
        return $result;
    }

    /**
     * @param $columnValues
     *
     * @return mixed
     */
    private function setDateModified( &$columnValues )
    {
        $dateModifiedColumn = $this->dateModifiedColumn;
        $dateModified = $this->getDb()->getNowString();
        if ( !empty( $this->dateModifiedColumn ) )
        {
            $columnValues[ $this->dateModifiedColumn ] = $dateModified;
            $this->$dateModifiedColumn = $dateModified;
        }
        return $columnValues;
    }

    /**
     * @param string $cacheKey
     *
     * @return static
     */
    public static function readFromCache( string $cacheKey )
    {
        $result = null;
        $dbRow = Cache::get( $cacheKey );
        if ( !empty( $dbRow ) )
        {
            $result = static::createInstance();
            $result->loadFromDbRow( $dbRow );
        }
        return $result;
    }

    /**
     * @param string $cacheKey
     *
     * @return static[]
     */
    public static function readRowsFromCache( string $cacheKey )
    {
        $result = null;
        $dbRows = Cache::get( $cacheKey );
        if ( !empty( $dbRows ) )
        {
            $result = [];
            foreach ( $dbRows as &$dbRow )
            {
                $row = static::createInstance();
                $row->loadFromDbRow( $dbRow );
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * @param string $cacheKey
     * @param Row[] $rows
     *
     * @param $expiration
     *
     * @return bool
     */
    public static function writeRowsToCache( $cacheKey, $rows, $expiration = self::DefaultCacheExpiration )
    {
        $dbRows = Row::toDbRows( $rows );
        $result = Cache::set( $cacheKey, $dbRows, $expiration );
        return $result;
    }

    /**
     * @param $cacheKey
     *
     * @param float|int $expiration
     *
     * Note: Be careful with the expiration!
     *
     * @return bool
     */
    public function writeToCache( $cacheKey, $expiration = self::DefaultCacheExpiration )
    {
//        $result = $this->isValidRow()
//            ? Cache::set( $cacheKey, $this->toDbRow( [] ), $expiration )
//            : false;

        $result = Cache::set( $cacheKey, $this->toDbRow( [] ), $expiration );

        return $result;
    }


}
