<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 7/19/2016
 * Time: 12:06 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data;

use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Data\Db\DbOperations;
use Com\Mh\Ds\Infrastructure\Data\Db\IDbOperations;


/**
 * Class RowFactory
 * @package Com\Mh\Ds\Infrastructure\Data
 */
class RowFactory implements IRowFactory
{
    use SingletonTrait;

    private $_log = false;
    /**
     * @var string[]
     */
    private $noDateModifiedTables = [];

    public function __construct() { }

    /**
     * @param $rowClass
     *
     * @return Row
     */
    public function create( $rowClass )
    {
        /** @var Row $result */
        $result = new $rowClass( $this->getDb() );
        $result->setLog( $this->_log );

        $result->setDateModifiedColumn(

            key_exists( $result->getFullTableName(), $this->noDateModifiedTables )
                ? ''
                : CommonColumns::DateModified );

        return $result;
    }


    /**
     * @param string[] $tables
     */
    public function setNoDateModifiedTables( array $tables )
    {
        $this->noDateModifiedTables = $tables;
    }


    /**
     * @return IDbOperations
     */
    public function getDb():IDbOperations
    {
        $result = DbOperations::getInstance();
        return $result;
    }

    /**
     * @param Boolean $value
     */
    public function setLog( bool $value )
    {
        $this->_log = $value;
    }

    /**
     * @return Boolean
     */
    public function getLog():bool
    {
        return $this->_log;
    }
}
