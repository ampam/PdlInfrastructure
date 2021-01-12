<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/16/2017
 * Time: 7:19 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

use Com\Mh\Ds\Infrastructure\Languages\Php\PhpUtils;

/**
 * Class NumericExpression
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
class NumericExpression extends Expression
{
    const Plus = '+';
    const Minus = '-';
    const UnaryMinus = '__-';
    const Mul = '*';
    const Div = '/';


    const Operators = [
        self::Plus => true,
        self::Minus => true,
        self::Mul => true,
        self::Div => true,
        self::UnaryMinus => true
    ];

    /**
     * NumericExpression constructor.
     *
     * @param Expression|null $parent
     */
    public function __construct( Expression $parent = null )
    {
        parent::__construct( $parent );
        $this->_stack = [];
    }


    /**
     * @param $token
     *
     * @return boolean
     */
    protected function isOper( $token )
    {
        $result = ( is_int( $token ) || is_string( $token ) ) && PhpUtils::keyExists( $token, self::Operators );
        return $result;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _plus( $value = null )
    {
        return $this->binaryInFix( self::Plus, $value );
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function _minus( $value = null )
    {
        return $this->binaryInFix( self::Minus, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _unaryMinus( $value = null )
    {
        return $this->unaryPrefix( self::UnaryMinus, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _mul( $value = null )
    {
        return $this->binaryInFix( self::Mul, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _div( $value = null )
    {
        return $this->binaryInFix( self::Div, $value );
    }

    /**
     * @param $token
     *
     * @return string
     */
    protected function processToken( $token )
    {
        switch ( true )
        {

            case $token === self::UnaryMinus:
                $result = '-';
                break;

            default:
                $result = $this->tokenToString( $token );
                break;
        }

        return $result;

    }




}
