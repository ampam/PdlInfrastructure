<?php


namespace Com\Mh\Laravel;


use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use Com\Mh\Ds\Infrastructure\Data\Db\DbUtils;
use Com\Mh\Ds\Infrastructure\Data\Db\IDbOperations;
use Com\Mh\Ds\Infrastructure\Data\Db\MySql\MySqlUtils;
use Com\Mh\Ds\Infrastructure\Data\Db\SqlOptions;
use Com\Mh\Ds\Infrastructure\Data\Row;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * Class DbOperations
 * @package Com\Mh\Laravel
 */
class LaravelDbOperations implements IDbOperations
{

    use SingletonTrait;

    /**
     * LaravelDbOperations constructor.
     */
    public function __construct()
    {
        DbUtils::setEscapeFunction( function ( $value ): string {
            $result = $this->escapeString( $value );
            return $result;
        } );
    }


    /**
     * @param $options
     *
     * @return array
     */
    public function selectOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $sql = SqlOptions::toSelect( $options );

        if ( isset( $options[ SqlOptions::Log ] ) && $options[ SqlOptions::Log ] === true )
        {
            Log::debug( $sql );
        }
        $result = DB::selectOne( $sql );
        return $result;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    public function updateOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $result = $this->update( $options );
        return $result;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    public function deleteOne( $options )
    {
        $options[ SqlOptions::Limit ] = 1;
        $result = $this->delete( $options );
        return $result;
    }

    /**
     * @param $options
     *
     * @return array
     */
    public function select( $options )
    {

        $sql = SqlOptions::toSelect( $options );

        if ( !empty( $options[ SqlOptions::Log ] ) )
        {
            Log::debug( $sql );
        }

        $result = DB::select( $sql );

        return $result;
    }

    /**
     * @param $options
     *
     * @return int
     */
    public function insert( $options )
    {
        $fields = DbUtils::fields2Insert( $options[ SqlOptions::Fields ], true );
        $table = $options[ SqlOptions::Table ];

        $sql = "INSERT INTO {$table} {$fields}";


        $success = Db::insert( $sql );

        $result = $success
            ? DB::getPdo()->lastInsertId()
            : -1;

        if ( is_numeric( $result ) )
        {
            $result = intval( $result, 10 );
        }

        return $result;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    public function update( $options )
    {
        $update = SqlOptions::toUpdate( $options );
        $result = DB::update( $update );
        return $result;
    }

    /**
     * @param $options
     *
     * @param bool $logIt
     *
     * @return mixed
     */
    public function multiInsert( $options, $logIt = false )
    {
        $result = [];
        /**
         * @var Row[]
         */
        $rows = $options[ SqlOptions::Rows ];
        $arrayRows = [];

        /**
         * @var Row $row
         */
        foreach ( $rows as $row )
        {
            $arrayRow = $row->toDbRowForInsert();
            $row->setDates( $arrayRow );

            $arrayRows[] = $arrayRow;
        }

        $table = Db::table( $options[ 'table' ] );
        $table->insert( $arrayRows );
        $id = DB::getPdo()->lastInsertId();

        $autoIncrementStep = Config::get( 'pdl.mysql.autoIncrementStep', 1 );

        for ( $index = 0; $index < count( $rows ); $index++ )
        {
            $result[ $index ] = $id;
            $id += $autoIncrementStep;
        }

        return $result;
    }

    /**
     * @param $options
     *
     * @param bool $logIt
     *
     * @return mixed
     */
    public function delete( $options, $logIt = false )
    {
        if ( empty( $options[ SqlOptions::Where ] ) )
        {
            Log::debug( "ALERT!! Attempt to erase whole table" );
            die();

        }

        $delete = SqlOptions::toDelete( $options );

        if ( $logIt )
        {
            Log::debug( $delete );
        }
        $result = DB::delete( $delete );

        return $result;
    }

    /**
     * @param $dateTimeParts
     * @param bool $doDate
     * @param bool $doTime
     *
     * @return string
     */
    public static function dateParts2String( $dateTimeParts, $doDate = true, $doTime = true )
    {
        $result = MySqlUtils::dateParts2Mysql( $dateTimeParts, $doDate, $doTime );
        return $result;
    }

    /**
     *
     */
    public function getNowString()
    {
        $now = time();
        $formattedNow = date( 'm/d/Y h:i:s A', $now );
        $result = self::dateParts2String( getdate( strtotime( $formattedNow ) ), true, true );
        return $result;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function escapeString( $value ): string
    {
        $quotedString = Db::getPdo()->quote( $value );
        $parts = explode( "'", $quotedString );
        $result = $parts[ 1 ] ?? $parts[ 0 ];
        return $result;
    }

}
