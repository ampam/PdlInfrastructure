<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 7/18/2016
 * Time: 2:03 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db;

/**
 * Interface IDbOperations
 * @package Com\Mh\Ds\Infrastructure\Data\Db
 */
interface IDbOperations
{
    public function selectOne( $options );
    public function updateOne( $options );
    public function deleteOne( $options );

    public function select( $options );
    public function insert( $options );
    public function update( $options );
    public function multiInsert( $options );
    public function delete( $options );

    public function getNowString();

}
