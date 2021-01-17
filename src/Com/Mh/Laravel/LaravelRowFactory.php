<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 7/19/2016
 * Time: 12:06 PM
 */

namespace Com\Mh\Laravel;



use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Data\Db\IDbOperations;
use Com\Mh\Ds\Infrastructure\Data\IRowFactory;
use Com\Mh\Ds\Infrastructure\Data\Row;

/**
 * Class RowFactory
 * @package Com\Mh\Laravel
 */
class LaravelRowFactory implements IRowFactory
{
    use SingletonTrait;

    private $_log = false;
    /**
     * @var string[]
     */
    private $noTimestampTables = [];

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

            key_exists( $result->getFullTableName(), $this->noTimestampTables )
                ? ''
                : LaravelCommonColumns::UpdatedAt );

        $result->setDateCreatedColumn(

            key_exists( $result->getFullTableName(), $this->noTimestampTables )
                ? ''
                : LaravelCommonColumns::CreatedAt );

        return $result;
    }


    /**
     * @param string[] $tables
     */
    public function setNoTimestampTables( array $tables )
    {
        $this->noTimestampTables = $tables;
    }


    /**
     * @return IDbOperations
     */
    public function getDb():IDbOperations
    {
        $result = LaravelDbOperations::getInstance();
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
