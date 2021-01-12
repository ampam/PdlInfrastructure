<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/15/2017
 * Time: 9:46 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

use Exception;
use Com\Mh\Ds\Infrastructure\Data\Db\DbUtils;
use Com\Mh\Ds\Infrastructure\Languages\Php\PhpUtils;


/**
 * Class BoolExpression
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
class BoolExpression extends NumericExpression
{
    const Equality = '=';
    const Inequality = '<>';
    const Gt = '>';
    const Gte = '>=';
    const Lt = '<';
    const Lte = '<=';
    const In = 'IN';
    const Greatest = 'GREATEST';
    const Interval = 'INTERVAL';
    const Least = 'LEAST';
    const Not = 'NOT';
    const _And = 'AND';
    const _Or = 'OR';
    const StrCmp = 'STRCMP';
    const IsNull = 'IS NULL';
    const IsNotNull = 'IS NOT NULL';
    const Is = 'IS';
    const IsNot = 'IS NOT';
    const Between = 'BETWEEN';
    const NotBetween = 'NOT BETWEEN';
    const Like = 'LIKE';

    const Operators = [
        self::Equality => true,
        self::Inequality => true,
        self::Gt => true,
        self::Gte => true,
        self::Lt => true,
        self::Lte => true,
        self::In => true,
        self::_And => true,
        self::_Or => true,
        self::Not => true,
        self::IsNull => true,
        self::IsNotNull => true,
        self::Is => true,
        self::IsNot => true,
        self::Between => true,
        self::Like => true,
        self::Least => true,
        self::Greatest => true,
        self::Interval => true
    ];

    const BinOper = [
        self::Equality => true,
        self::Gt => true,
        self::Gte => true,
        self::Lt => true,
        self::Lte => true,
        self::Inequality => true,
        self::In => true,
        self::Like => true,
        self::Least => true,
        self::Greatest => true,
        self::Interval => true,
        self::_And => true,
        self::_Or => true,
        self::Is => true,
        self::IsNot => true,
        self::StrCmp => true
    ];

    const Unary = [
        self::Not => true,
        self::IsNull => true,
        self::IsNotNull => true
    ];

    const Ternary = [
        self::Between => true,
        self::NotBetween => true
    ];
    const ExcludeOperators = [
        self::IsNotNull => true
    ];


    /**
     * ColumnBoolOperations constructor.
     *
     * @param BoolExpression|null $parent
     */
    public function __construct( BoolExpression $parent = null )
    {
        parent::__construct( $parent );
        $this->_stack = [];
    }


    /**
     * @param $column
     * @param $param
     *
     * @return $this
     */
    public function _addColumnTerm( $column, $param = null  )
    {
        $this->_addTerm( $column instanceof Column
            ? $column->name
            : $column );

        if ( is_array( $param ) )
        {
            $this->_in( array_unique( $param ) );
        }
        else if ( $param !== null )
        {
            $this->_eq( $param );
        }

        return $this;
    }


    /**
     * @return BoolExpression
     */
    public function _expression()
    {
        //TODO return null for now because there are no other elements
        return null;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    protected function isOper( $token )
    {
        $result = parent::isOper( $token ) || PhpUtils::keyExists( $token, self::Operators );
        return $result;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    protected function isOperator( $token )
    {

        $result = !is_array( $token ) && $this->isOper( $token ) &&
            !PhpUtils::keyExists( $token, self::ExcludeOperators );
        return $result;
    }


    /**
     * @param $value
     *
     * @return $this
     */
    public function _eq( $value = null )
    {
        return $this->binaryInFix( self::Equality, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _neq( $value = null )
    {
        return $this->binaryInFix( self::Inequality, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _in( $value = null )
    {
        return $this->binaryInFix( self::In, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _greatest( $value = null )
    {
        return $this->binaryInFix( self::Greatest, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _interval( $value = null )
    {
        return $this->binaryInFix( self::Interval, $value );
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function _least( $value = null )
    {
        return $this->binaryInFix( self::Least, $value );
    }


    /**
     *
     * @return $this
     */
    public function _not()
    {
        return $this->unaryPrefix( self::Not );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _or( $value = null )
    {
        return $this->binaryInFix( self::_Or, $value );
    }


    /**
     *
     * @param $rightSide
     *
     * @return $this
     */
    public function _and( $rightSide = null )
    {
        return $this->binaryInFix( self::_And, $rightSide );
    }

    /**
     *
     * @param $rightSide
     *
     * @return $this
     */
    public function _gt( $rightSide = null )
    {
        return $this->binaryInFix( self::Gt, $rightSide );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _gte( $value = null )
    {
        return $this->binaryInFix( self::Gte, $value );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _lt( $value = null )
    {
        return $this->binaryInFix( self::Lt, $value );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _lte( $value = null )
    {
        return $this->binaryInFix( self::Lte, $value );
    }

    /**
     *
     * @return $this
     */
    public function _isNull()
    {
        return $this->unaryPostfix( self::IsNull );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _is( $value = null )
    {
        return $this->binaryInFix( self::Is, $value );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _isNot( $value = null )
    {
        return $this->binaryInFix( self::IsNot, $value );
    }

    /**
     *
     * @param $value
     *
     * @return $this
     */
    public function _like( $value = null )
    {
        return $this->binaryInFix( self::Like, $value );
    }

    /**
     *
     * @param $value1
     * @param $value2
     *
     * @return $this
     */
    public function _between( $value1 = null, $value2 = null )
    {
        return $this->ternary( self::Between, $value1, $value2 );
    }

    /**
     *
     * @param $value1
     * @param $value2
     *
     * @return $this
     */
    public function _strcmp( $value1 = null, $value2 = null )
    {
        return $this->binaryPrefixOper( self::StrCmp, $value1, $value2 );
    }

    /**
     *
     * @param $value1
     * @param $value2
     *
     * @return $this
     */
    public function _notBetween( $value1 = null, $value2 = null )
    {
        return $this->ternary( self::NotBetween, $value1, $value2 );
    }

    /**
     *
     * @return $this
     */
    public function _isNotNull()
    {
        return $this->unary( self::IsNotNull );
    }


    /**
     * @param $token
     *
     * @return string
     */
    protected function buildStringToken( $token )
    {
        $result = '"' . DbUtils::escapeString( $token ) . '"';
        return $result;
    }

    /**
     * @param $token
     *
     * @return string
     *
     * @throws Exception
     */
    protected function processToken( $token )
    {

        switch( true )
        {
            case $token === self::Interval:
            case $token === self::Greatest:
            case $token === self::Least:
            case $token === self::In:
                $result = $this->emitListBasedSyntax( $token );
                break;

            case $token === self::NotBetween:
            case $token === self::Between:
                $result = $this->emitBetweenSyntax( $token );
                break;

            case $token === self::StrCmp:
                $result = $this->emitStrCmp( $token );
                break;


            default:
                $result = parent::processToken( $token );
                break;
        }

        return $result;
    }

    /**
     * @param string|array $elements
     *
     * @return string
     */
    private function processListToken( $elements )
    {
        if ( !is_array( $elements ) )
        {
            $elements = explode( ',', $elements );
        }

        $result = DbUtils::createSafeINList( $elements );

        return $result;

    }

    /**
     * @param $token
     *
     * @return string
     *
     * @throws Exception
     */
    private function emitBetweenSyntax( $token )
    {
        $value1 = $this->tokenToString( $this->getToken() );
        $value2 = $this->tokenToString( $this->getToken() );
        $result = "{$token} {$value1} AND {$value2}";
        return $result;
    }

    /**
     * @param $token
     *
     * @return string
     *
     * @throws Exception
     */
    private function emitListBasedSyntax( $token )
    {
        $list = $this->processListToken( $this->getToken() );
        $result = "{$token}( {$list} )";
        return $result;
    }

    /**
     * @param $token
     *
     * @return string
     *
     * @throws Exception
     */
    private function emitStrCmp( $token )
    {
        $operand1 = $this->tokenToString( $this->getToken() );
        $operand2 = $this->tokenToString( $this->getToken() );
        $result = "{$token}( {$operand1}, {$operand2} )";
        return $result;
    }

    /**
     * @param Expression $subExpression
     *
     * @return $this
     */
    public function _subExpression( $subExpression )
    {
        $this->addDefaultAndOperator();

        parent::_subExpression( $subExpression );

        return $this;
    }

    /**
     * @param $term
     *
     * @return $this
     */
    public function _addTerm( $term )
    {
        $this->addDefaultAndOperator();

        parent::_addTerm( $term );

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        $result = empty( $this->_stack );
        return $result;
    }


    /**
     *
     */
    private function addDefaultAndOperator()
    {
        if ( !empty( $this->_stack ) )
        {
            $previewsToken = end( $this->_stack );
            if ( !$this->isOperator( $previewsToken ) )
            {
                $this->_and();
            }
        }
    }
}
