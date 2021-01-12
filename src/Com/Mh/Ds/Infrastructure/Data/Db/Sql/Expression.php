<?php
/**
 * Created by PhpStorm.
 * User: am
 * Date: 9/16/2017
 * Time: 7:22 PM
 */

namespace Com\Mh\Ds\Infrastructure\Data\Db\Sql;

use Exception;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;
use Com\Mh\Ds\Infrastructure\Languages\Php\PhpUtils;


/**
 * Class Expression
 * @package Com\Mh\Ds\Infrastructure\Data\Db\Sql
 */
abstract class Expression implements ISqlFragment
{

    protected $_stackIndex = 0;

    protected $_parent;

    /** @var  array */
    protected $_stack;

    /** @var  array */
    protected $_variables;

    /** @var  array */
    protected $_functions;

    protected $log = true;

    /** @var  array */
    private $_keywords;

    /**
     * Expression constructor.
     *
     * @param Expression|null $parent
     */
    protected function __construct( Expression $parent = null )
    {
        $this->_parent = $parent;
        $this->_stack = [];

        $this->_variables = [];
        $this->_functions = [];
        $this->_keywords = [];
    }

    /**
     * @param $value
     */
    public function log( $value )
    {
        if ( $this->log )
        {
            Debug::log( $value );
        }
    }

    /**
     * @param $string
     * @param Expression $expression
     *
     * @throws Exception
     */
    public static function throwException( $string, Expression $expression )
    {
        Debug::log( $string );
        Debug::log( "Expression pointer: " . $expression->_stackIndex );
        Debug::log( "Expression stack: " );
        Debug::log( $expression->_stack );
        Debug::logCallStack();
        throw new Exception( $string );
    }

    /**
     * @param $token
     *
     * @return boolean
     */
    protected abstract function isOper( $token );

    /**
     * @param $oper
     * @param $rightSide
     *
     * @return $this
     */
    protected function binaryInFix( $oper, $rightSide )
    {
        $this->_stack[] = $oper;
        if ( $rightSide !== null )
        {
            $this->_stack[] = $rightSide;
        }
        return $this;
    }

    /**
     * @param $oper
     * @param $operand1
     * @param $operand2
     *
     * @return $this
     */
    protected function binaryPrefixOper( $oper, $operand1, $operand2 )
    {
        $this->_stack[] = $oper;
        $this->_stack[] = $operand1;
        $this->_stack[] = $operand2;
        return $this;
    }


    /**
     * @param $oper
     * @param $value1
     * @param $value2
     *
     * @return $this
     */
    protected function ternary( $oper, $value1, $value2 )
    {
        $this->_stack[] = $oper;
        if ( $value1 !== null )
        {
            $this->_stack[] = $value1;
            if ( $value2 !== null )
            {
                $this->_stack[] = $value2;
            }
        }
        return $this;
    }

    /**
     * @param $oper
     *
     * @return $this
     */
    protected function unary( $oper )
    {
        $this->_stack[] = $oper;
        return $this;
    }

    /**
     * @param $oper
     *
     * @param null $leftSide
     *
     * @return $this
     */
    protected function unaryPostfix( $oper, $leftSide = null )
    {
        if ( $leftSide )
        {
            $this->_stack[] = $leftSide;
        }

        $this->unary( $oper );

        return $this;
    }

    /**
     * @param $oper
     *
     * @param null $rightSide
     *
     * @return $this
     */
    protected function unaryPrefix( $oper, $rightSide = null )
    {
        $this->unary( $oper );
        if ( $rightSide )
        {
            $this->_stack[] = $rightSide;
        }

        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function getToken()
    {
        if ( $this->endOfStack() )
        {
            Expression::throwException( "Invalid Logical Expression", $this );
        }

        $result = $this->_stack[ $this->_stackIndex ];
        $this->_stackIndex++;

        return $result;
    }


    /**
     * @param string|BoolExpression|RightSide $token
     *
     * @return string
     * @throws Exception
     */
    protected function tokenToString( $token )
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $result = '';

        if ( $this->isExpression( $token ) )
        {
            $result = '( ' . $token->_toString() . ' )';
        }
        else if ( $this->isRightSide( $token ) )
        {
            $result = $token->value;
        }
        else if ( $this->isOper( $token ) || $this->isKeyword( $token ) ||
                    $this->isVariable( $token ) || $this->isFunctionCall( $token ) )
        {
            $result = $token;
        }
        else
        {
            $result = $this->literalToString( $token );
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function endOfStack()
    {
        $result = $this->_stackIndex >= count( $this->_stack );
        return $result;
    }

    /**
     * @return Expression
     */
    public function _end()
    {
        $result = $this->_parent !== null
            ? $this->_parent
            : $this;

        return $result;
    }

    /**
     * @param $term
     *
     * @return $this
     */
    public function _addTerm( $term )
    {
        $this->_stack[] = $term;
        return $this;
    }

    /**
     * @param Expression $subExpression
     *
     * @return $this
     */
    public function _subExpression( $subExpression )
    {
        $this->_stack[] = $subExpression;
        return $this;
    }

    /**
     * @param $token
     *
     * @return string
     */
    protected function buildStringToken( $token )
    {
        $result = "\"{$token}\"";
        return $result;
    }

    /**
     * @param $token
     *
     * @return string
     * @throws Exception
     */
    protected function processToken( $token )
    {
        $result = $this->tokenToString( $token );
        return $result;
    }

    /**
     *
     * @throws Exception
     */
    public function _toString()
    {

        $parts = [];
        $this->_stackIndex = 0;

        while ( !$this->endOfStack() )
        {
            $token = $this->getToken();
            $value = $this->processToken( $token );
            $parts[] = $value;
        }

        $result = implode( ' ', $parts );

        return $result;
    }


    /**
     * @param $token
     *
     * @return bool
     */
    protected function isKeyword( $token )
    {
        $result = in_array( $token, $this->_keywords );
        return $result;
    }

    /**
     * @param $token
     *
     * @return string
     * @throws Exception
     */
    private function literalToString( $token )
    {
        $result = $token;
        if ( is_string( $token ) )
        {
            $result = $this->buildStringToken( $token );
        }
        else if ( PhpUtils::isBool( $token ) || PhpUtils::isScalar( $token ) )
        {
            $result = (string)$token;
        }
        else if ( PhpUtils::isValueType( $token ) )
        {
            $result = (string)$token;
        }
        else
        {
            self::throwException( "Invalid Token Type in Logical Expression", $this );
        }
        return $result;

    }


    /**
     * @param $token
     *
     * @return bool
     */
    protected function isFunctionCall( $token )
    {
        $result = in_array( $token, $this->_functions );
        return $result;
    }


    /**
     * @param $token
     *
     * @return bool
     */
    protected function isVariable( $token )
    {
        $result = PhpUtils::keyExists( $token, $this->_variables );
        return $result;
    }

    /**
     * @param $name
     * @param $type
     */
    public function _addVariable( $name, $type )
    {
        $this->_variables[ $name ] = $type;
    }

    /**
     * @param $variables
     */
    public function _addVariables( $variables )
    {
        foreach ( $variables as $name => $type )
        {
            $this->_addVariable( $name, $type );
        }
    }

    /**
     * @param $name
     */
    public function _addKeyword( $name )
    {
        $this->_keywords[ $name ] = true;
    }

    /**
     * @param $name
     * @param array $arguments
     */
    public function _addFunctions( $name, $arguments )
    {
        $this->_variables[ $name ] = $arguments;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private function isRightSide( $token )
    {
        $result = $token instanceof RightSide;
        return $result;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private function isExpression( $token )
    {
        $result = $token instanceof Expression;
        return $result;
    }


}
