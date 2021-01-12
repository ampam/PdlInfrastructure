<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 2/28/2017
 * Time: 11:35 AM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\MySql;

/**
 * Class ColumnInfo
 * @property string $field
 * @property string $type
 * @property string $null
 * @property string $key
 * @property string $default
 * @property string $extra
 * @property string $name
 * @package Com\Mh\Ds\Infrastructure\Data\Db\MySql
 */
class ColumnInfo
{

    /**
     * ColumnInfo constructor.
     *
     * @param $rawInfo
     */
    public function __construct( $rawInfo )
    {
        $this->field = $rawInfo[ 'Field' ];
        $this->type = $rawInfo[ 'Type' ];
        $this->null = $rawInfo[ 'Null' ];
        $this->key = $rawInfo[ 'Key' ];
        $this->default = $rawInfo[ 'Default' ];
        $this->extra = $rawInfo[ 'Extra' ];
        $this->name = $this->field;
    }

    /**
     * @return string
     */
    public function getMainType(): string
    {
        $parts = explode( '(', $this->type );
        $result = strtoupper( $parts[ 0 ] );
        return $result;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey()
    {
        $result = $this->key == 'PRI' && stripos( $this->extra, 'auto_increment' ) !== false;
        return $result;
    }

    /**
     * @return bool
     */
    public function isDate()
    {
        $result = $this->type == 'date' ||
            $this->type == 'datetime' ||
            $this->type == 'timestamp';

        return $result;
    }

}
