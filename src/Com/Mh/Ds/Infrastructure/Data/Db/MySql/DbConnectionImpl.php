<?php

namespace Com\Mh\Ds\Infrastructure\Data\Db\MySql;

use Com\Mh\Ds\Infrastructure\Data\Db\IDbConnection;
use Exception;
use mysqli;
use mysqli_result;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;
use Com\Mh\Ds\Infrastructure\Http\Inputs;
use Com\Mh\Ds\Infrastructure\Languages\Php\Boolean;

/**
 * Class DbConnectionImpl
 * @package Com\Mh\Ds\Infrastructure\Data\Db\MySql
 */
class DbConnectionImpl implements IDbConnection
{

    private $dbName = '';

    private $ownsSlave = false;

    /**
     * @var mysqli
     */
    private $currentMySqli = null;

    /** @var string[] */
    private $queryLogs = [];

    private $isQueryLogEnabled = false;
    private $writeMode = false;

    private $sql;
    private $dbConf;
    private $totalQueryTime = 0;

    /**
     * @var mysqli
     */
    private $masterMySqli;

    /**
     * @var mysqli
     */
    private $slaveMySqli;
    private $selectedMasterServer;
    private $selectedSlaveServer;

    /**
     * @param $dbConf
     *
     * @return bool
     */
    public function connect( array $dbConf )
    {

        $this->dbConf = $dbConf;

        if ( Inputs::get( 'show_sql' ) === 'Y' )
        {
            $this->enableQueryLog( true );
        }

        $this->dbName = $dbConf[ 'db_name' ];
        $masters = $dbConf[ 'masters' ];

        $this->selectedMasterServer = $masters[ rand( 0, count( $masters ) - 1 ) ];
        $this->masterMySqli = $this->connectToServer( $this->selectedMasterServer );

        if ( empty( $this->masterMySqli ) )
        {
            $this->outputMasterConnectionError();
            return false;
        }

        $slaves = $dbConf[ 'slaves' ];
        if ( !empty( $slaves ) )
        {
            $this->selectedSlaveServer = $slaves[ rand( 0, count( $slaves ) - 1 ) ];
            $this->slaveMySqli = $this->connectToServer( $this->selectedSlaveServer );
        }

        if ( !empty( $this->slaveMySqli ) )
        {
            $this->ownsSlave = true;
        }
        else
        {
            $this->slaveMySqli = $this->masterMySqli;
            $this->ownsSlave = false;
        }

        $this->currentMySqli = $this->masterMySqli;

        $this->selectDb();

        return true;
    }

    /**
     * @return int
     */
    public function getTotalQueryTime()
    {
        return $this->totalQueryTime;
    }

    /** @noinspection PhpUnused */
    /**
     * @return bool
     */
    public function isQueryLogEnabled()
    {
        return $this->isQueryLogEnabled;
    }

    /**
     * @return string[]
     */
    public function getQueryLogs()
    {
        return $this->queryLogs;
    }

    /**
     * @param $server
     *
     * @return mysqli
     */
    private function connectToServer( $server )
    {

        $result = new mysqli( "{$server['server']}:{$server['port']}",
            $this->dbConf[ 'user_name' ],
            $this->dbConf[ 'password' ] );

        if ( $result->connect_errno )
        {
            echo "can't connect to db : {$server['server']}!!";
            Debug::log( "can't connect to db: {$server['server']}!!" );
            die;
        }

        $result->set_charset( $this->dbConf['chatSet'] );
        $result->query( "SET NAMES '{$this->dbConf['chatSet']}'" );
        $result->query( "SET CHARACTER_SET_CLIENT {$this->dbConf['chatSet']}" );

        return $result;
    }

    /**
     * @param $enable
     */
    public function enableQueryLog( $enable )
    {
        $this->isQueryLogEnabled = $enable;
    }

    /**
     *
     * @return bool
     */
    private function selectDb()
    {
        $result = $this->masterMySqli->select_db( $this->dbName );

        if ( $result && ( $this->slaveMySqli !== $this->masterMySqli ) )
        {
            $result = $this->slaveMySqli->select_db( $this->dbName );
        }
        return $result;
    }

    /**
     *
     * @return mysqli_result|bool
     * @throws Exception
     */
    private function &executeQuery()
    {
        $startTime = 0;

        if ( $this->isQueryLogEnabled )
        {
            $startTime = microtime( true );
        }

        $this->currentMySqli = $this->writeMode
            ? $this->masterMySqli
            : $this->slaveMySqli;

        $result = $this->currentMySqli->query( $this->sql );

        if ( $this->isQueryLogEnabled )
        {
            $elapsedMs = 1000 * ( microtime( true ) - $startTime );
            $queryType = $this->writeMode
                ? "/* WRITE {$elapsedMs} ms */  "
                : "/* READ {$elapsedMs} ms */  ";

            $this->queryLogs[] = $queryType . trim( $this->sql );
            $this->totalQueryTime += $elapsedMs;
        }

        if ( !$result )
        {
            $this->displayError();
            $result = null;
        }

        return $result;
    }

    /**
     * @return array|string[]
     */
    public function getVersions()
    {
        $result = [];
        $result[] = "m: {$this->selectedMasterServer['server']}:{$this->selectedMasterServer['port']} v"
            . $this->masterMySqli->get_server_info();

        if ( $this->masterMySqli !== $this->slaveMySqli )
        {
            $result[] = "s: {$this->selectedSlaveServer['server']}:{$this->selectedSlaveServer['port']} v"
                . $this->slaveMySqli->get_server_info();
        }
        return $result;
    }

    /**
     * @param $sql
     *
     * @return mysqli_result|bool|mixed
     * @throws Exception
     */
    public function executeWrite( $sql )
    {
        $this->sql =& $sql;
        $this->writeMode = true;

        $result = $this->executeQuery();
        return $result;
    }

    /**
     * @param $sql
     *
     * @return bool|mysqli_result|mixed
     * @throws Exception
     */
    public function executeRead( $sql )
    {
        $this->sql =& $sql;
        $this->writeMode = false;

        $result = $this->executeQuery();

        return $result;
    }

//    /**
////     * @param $sql
////     * @param bool $buffered
////     *
////     * @throws Exception
////     */
////    public function executeAllServers( $sql, $buffered = true )
////    {
////        $this->executeWrite( $sql, $buffered );
////        if ( $this->dbLink != $this->slaveDbLink )
////        {
////            $this->executeRead( $sql, $buffered );
////        }
////    }

    /**
     * @param $sql
     *
     * @return array
     * @throws Exception
     */
    public function &getOne( $sql )
    {
        $this->sql =& $sql;
        $this->writeMode = false;

        $result = $this->executeQuery();

        $returnArray = $this->fetchArray( $result );

        $this->freeResult( $result );

        return $returnArray;
    }

    /**
     * @param $sql
     *
     * @return array
     * @throws Exception
     */
    public function getAllRows( $sql )
    {
        $result = [];

        $sqlResult = $this->executeRead( $sql );

        while ( $row = $this->fetchArray( $sqlResult ) )
        {
            $result[] = $row;
        }

        $this->freeResult( $sqlResult );

        return $result;
    }

//    function num_rows( $result )
//    {
//        return @mysql_num_rows( $result );
//    }
//
//    function num_fields( $result )
//    {
//        return @mysql_num_fields( $result );
//    }

    /**
     * @return int
     */
    public function insertId()
    {
        return $this->currentMySqli->insert_id;
    }

    /**
     * @return int[]
     *
     * this function is not 100% bullet proof
     * @throws Exception
     */
    public function insertIds()
    {
        $affectedRowCount = $this->currentMySqli->affected_rows;
        $firstInsertedId = $this->currentMySqli->insert_id;

        $row = $this->getOne( "SELECT @@auto_increment_increment" );
        $incrementStep = $row[ '@@auto_increment_increment' ];

        $result = [];

        if ( $affectedRowCount > 0 )
        {
            for ( $i = 0; $i < $affectedRowCount; $i++ )
            {
                $result[] = $firstInsertedId + $i * $incrementStep;
            }
        }

        return $result;
    }

    /**
     * @param $str
     *
     * @return string
     */
    public function escapeString( $str )
    {
        $result = $this->currentMySqli->real_escape_string( $str );
        return $result;
    }

    /**
     * @return bool
     */
    public function close()
    {
        $result = $this->masterMySqli->close();
        $this->masterMySqli = null;
        $this->currentMySqli = null;

        if ( $this->ownsSlave )
        {
            $result = $this->slaveMySqli->close();
            $this->slaveMySqli = null;
            $this->ownsSlave = false;
        }

        return $result;
    }

    /**
     * @param $result
     *
     * @return array
     */
    private function fetchArray( $result )
    {
        $result = mysqli_fetch_array( $result, MYSQLI_ASSOC );
        return $result;
    }

//    function fetch_row( $result )
//    {
//        return @mysql_fetch_row( $result );
//    }

    /**
     * @param $result
     */
    private function freeResult( $result )
    {
        $this->sql = '';
        $this->writeMode = false;

        mysqli_free_result( $result );
    }

    /**
     * @return int
     */
    public function affectedRows()
    {
        $result = $this->currentMySqli->affected_rows;
        return $result;
    }

    /**
     * @return string
     */
    private function getErrorMessage()
    {
        $result = $this->currentMySqli->error;
        return $result;
    }

    /**
     * @return int|string
     */
    private function getErrorNumber()
    {
        $result = $this->currentMySqli->errno;
        return $result;
    }


    /**
     * @throws Exception
     */
    private function displayError()
    {
        /** @noinspection DuplicatedCode */
        $errorString = $this->getErrorNumber() . " - " . $this->getErrorMessage() . "<br/>\n<br/>\n";

        if ( !empty( $_SERVER[ 'HTTP_HOST' ] ) && !$this->isAjaxRequest() )
        {
            echo $errorString;
            echo $this->sql;
        }
        else
        {
            Debug::log( "MySql Error=>" );
            Debug::log( $errorString );
            Debug::log( $this->sql );
        }

        throw new Exception( $errorString, E_USER_ERROR );

    }

    /**
     * @return bool
     */
    private function isAjaxRequest()
    {
        return Inputs::get( 'ajax' ) === Boolean::ShortYes;
    }

    /**
     *
     */
    protected function outputMasterConnectionError()
    {
        global $gConf;

        echo "error connecting to master<pre>";

        if ( $gConf[ 'dev_mode' ] )
        {
            print_r( $gConf[ 'mysql' ] );
        }
    }

}

