<?php

namespace Com\Mh\Ds\Infrastructure\Data\Db;

use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Data\Db\MySql\MySqlUtils;
use Exception;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;


/**
 * Class DbOperations
 * @package Com\Mh\Ds\Infrastructure\Data\Db
 */
class DbOperations implements IDbOperations
{
    use SingletonTrait;

    private $logSelectTime;

    /** @var  IDbConnection */
    private $dbConnection;

    /**
     *
     */
    public function init()
    {
        $this->logSelectTime = false;
        $this->dbConnection = DbUtils::getDbConnection();
    }

    /**
     *
     */
    public function enableLogSelectTime()
    {
        $this->logSelectTime = true;
    }

    /** @noinspection PhpUnused */
    /**
     *
     */
    public function disableLogSelectTime()
    {
        $this->logSelectTime = false;
    }

    /**
     * @param $options
     *
     * @return array
     * @throws Exception
     */
    public function &select( $options )
    {
        $time = microtime( true );

        $sql = SqlOptions::toSelect( $options );

        if ( !empty( $options[ SqlOptions::Log ] ) )
        {
            Debug::log( $sql );
        }

        $result = $this->dbConnection->getAllRows( $sql );

        if ( $this->logSelectTime )
        {
            $time = round( ( microtime( true ) - $time ) * 1000, 3 );
            Debug::log( "Db OPERATION: {$sql}, ms: {$time}" );
        }

        return $result;
    }

    /**
     * @param $options
     *
     * @return array
     * @throws Exception
     */
    public function selectOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $sql = SqlOptions::toSelect( $options );

        if ( isset( $options[ SqlOptions::Log ] ) && $options[ SqlOptions::Log ] === true )
        {
            Debug::log( $sql );
        }
        $result = $this->rawSelectOne( $sql );
        return $result;
    }

    /**
     * @param $options
     * @param bool $escapeWithMySql
     *
     * @return int
     * @throws Exception
     */
    public function insert( $options, $escapeWithMySql = false )
    {
        $fields = DbUtils::fields2Insert( $options[ SqlOptions::Fields ], $escapeWithMySql );
        $table = $options[ SqlOptions::Table ];

        $sql = "INSERT INTO {$table} {$fields}";

        $this->dbConnection->executeWrite( $sql );

        $result = $this->dbConnection->insertId();

        return $result;
    }

    /**
     * @param $options
     *
     * @return int
     * @throws Exception
     */
    public function update( $options )
    {
        $update = SqlOptions::toUpdate( $options );

        $this->dbConnection->executeWrite( $update );
        $result = $this->dbConnection->affectedRows();
        return $result;
    }

    /**
     * @param $options
     *
     * @return int
     * @throws Exception
     */
    public function updateOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $result = $this->update( $options );
        return $result;
    }

   /**
     * @param $options
     * @param bool $logIt
     *
     * @return int
     * @throws Exception
     */
    public function delete( $options, $logIt = true )
    {
        if ( empty( $options[ SqlOptions::Where ] ) )
        {
            Debug::log( "ALERT!! Attempt to erase whole table" );
            die();

        }

        $delete = SqlOptions::toDelete( $options );

        if ( $logIt )
        {
            Debug::log( $delete );
        }

        $this->dbConnection->executeWrite( $delete );

        $result = $this->dbConnection->affectedRows();

        return $result;
    }

    /**
     * @param $options
     *
     * @return int
     * @throws Exception
     */
    public function deleteOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $result = $this->delete( $options );
        return $result;
    }

    /**
     * @param $options
     * @param bool $logIt
     *
     * @return array
     * @throws Exception
     */
    public function multiInsert( $options, $logIt = false )
    {
        $sql = SqlOptions::toMultiInsert( $options );

        if ( $logIt )
        {
            Debug::log( $sql );
        }

        $this->dbConnection->executeWrite( $sql );


        $result = $this->dbConnection->insertIds();

        return $result;

    }

    /**
     * @param $options
     * $options.table
     * $options.ids[]
     * $options.fields[]
     *      'column1' => 'value1'
     *      ......
     * $options.where optional
     *
     * @throws Exception
     */

    public function multiUpdateByIds( $options )
    {

        $updateSet = DbUtils::fields2Update( $options[ SqlOptions::Fields ] );
        $ids = DbUtils::createSafeINList( $options[ SqlOptions::Ids ] );

        $table = $options[ SqlOptions::Table ];
        $primaryKey = DbUtils::getPrimaryKeyField( $table );
        $where = SqlOptions::processWhere( $options );

        $sql = "UPDATE {$table} {$updateSet} WHERE {$primaryKey} IN ( $ids ) {$where}";

        $this->dbConnection->executeWrite( $sql );

    }

    /**
     * @param string $select
     *
     * @return array
     * @throws Exception
     */
    public function rawSelect( string $select )
    {
        $result = $this->dbConnection->getAllRows( $select );
        return $result;
    }

    /**
     * @param string $select
     *
     * @return array
     * @throws Exception
     */
    public function rawSelectOne( string $select )
    {
        $result = $this->dbConnection->getOne( $select );
        return $result;
    }


    /**
     *
     */
    public function getNowString()
    {
        $result = MySqlUtils::getNowString();
        return $result;
    }

}
