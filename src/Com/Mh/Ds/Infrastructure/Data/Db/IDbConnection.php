<?php

namespace Com\Mh\Ds\Infrastructure\Data\Db;


use Exception;

/**
 * Class IMySql
 * @package Com\Mh\Ds\Infrastructure\Data\Db
 */
interface IDbConnection
{
    /**
     * @param array $dbConf
     *
     * @return bool
     */
    public function connect( array $dbConf );

    /**
     * @return int
     */
    public function getTotalQueryTime();

    /**
     * @return bool
     */
    public function isQueryLogEnabled();

    /**
     * @return string[]
     */
    public function getQueryLogs();

    /**
     * @param $enable
     */
    public function enableQueryLog( $enable );

    /**
     * @param $sql
     *
     * @return mixed
     * @throws Exception
     */
    public function executeWrite( $sql );

    /**
     * @param $sql
     *
     * @return mixed
     * @throws Exception
     */
    public function executeRead( $sql );

    /**
     * @param $sql
     *
     * @return array
     * @throws Exception
     */
    public function getOne( $sql );

    /**
     * @param $sql
     *
     * @return array
     * @throws Exception
     */
    public function getAllRows( $sql );

    /**
     * @return int
     */
    public function insertId();

    /**
     * @return int[]
     *
     * this function is not 100% bullet proof
     * @throws Exception
     */
    public function insertIds();

    /**
     * @param $str
     *
     * @return string
     */
    public function escapeString( $str );

    /**
     * @return string[]
     */
    public function getVersions();

    public function close();

    public function affectedRows();
}
