<?php
[header]
namespace [namespace-name];

[use-block]
/**
[phpdoc-class]
[phpdoc-inheritance]
[phpdoc-package]
 *
[property-list] 
 *
 */
class [class-name] [inheritance]
{

    /**
     * [class-name] constructor.
     *
     * @param array $arguments
     */
    function __construct( ...$arguments )
    {
		$this->_dbName = "pam_db";
        $this->_className = "[class-name]";
        [property-attributes]

        parent::__construct( ...$arguments );
    }

    /**
     * @return self
     */
    public static function blankInstance()
    {
        /** @var self $result */
        $result = parent::_blankInstance( self::class );
        return $result;
    }

    /**
     *
     * @param array $selectOptions
     *
     * @return array[]
     */
    public static function loadDbRows( $selectOptions )
    {
        return self::_loadDbRows( $selectOptions, self::class );
    }

    /**
     *
     * @param array $ids
     *
     * @return array
     */
    public static function loadDbRowsFromIds( $ids )
    {
        return self::_loadDbRowsFromIds( $ids, self::class );
    }

    /**
     *
     * @param array $selectOptions
     *
     * @param bool $byDbId
     *
     * @return self[]
     */
    public static function loadRows( $selectOptions, $byDbId = false )
    {
        /** @var  self[] $result */
        $result = parent::_loadRows( $selectOptions, $byDbId, self::class );
        return $result;
    }

    /**
     *
     * @param array $ids
     *
     * @return self[]
     */
    public static function loadFromIds( $ids )
    {
        /** @var self[] $result */
        $result = parent::_loadFromIds( $ids, self::class );
        return $result;
    }

    /**
     *
     * @returns self
     */
    public static function createInstance()
    {
        /** @var self $result */
        $result = parent::_createInstance( self::class );
        return $result;
    }

    /**
     * @param $dbId
     * @param string|array $fields
     * @return self
     */
    public static function loadRow( $dbId, $fields = null )
    {
        /** @var self $result */
        $result = self::_loadRow( $dbId, $fields, self::class );
        return $result;
    }


    /**
     * @param String $where
     * @param string|array $fields
     * @return self
     */
    public static function loadRowWhere( $where, $fields = null )
    {
        /** @var self $result */
        $result = self::_loadRowWhere( $where, $fields, self::class );
        return $result;
    }

    /**
     * @param array $selectOptions
     * @return array
     */
    public static function loadDbRow( $selectOptions )
    {
        return self::_loadDbRows( $selectOptions, self::class );
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return self::_getTableName( self::class );
    }

    /**
     * @param array $deleteOptions
     * @return int
     */
    public static function deleteWhere( $deleteOptions )
    {
        return self::_deleteWhere( $deleteOptions, self::class );
    }

    /**
     * @param array $updateOptions
     * @return int
     */
    public static function multiUpdate( $updateOptions )
    {
        return self::_multiUpdate( $updateOptions, self::class );
    }

    /**
     * @return [class-name]BoolExpression
     */
    public static function where()
    {
        $result = new [class-name]BoolExpression();
        return $result;
    }

    /**
     * @return [class-name]ColumnsList
     */
    public static function columnList()
    {
        $result = new [class-name]ColumnsList();
        return $result;
    }

    [method-list]
}

