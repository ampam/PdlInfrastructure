<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/3/2017
 * Time: 3:09 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock;

use Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\Tags\MhProperty;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Class PropertyParser
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock
 */
class PropertyParser
{
    private $body;
    private $typeResolver;
    private $descriptionFactory;
    private $context;
    private $tokens;

    /**
     * PropertyParser constructor.
     *
     * @param $body
     * @param TypeResolver|null $typeResolver
     * @param DescriptionFactory|null $descriptionFactory
     * @param TypeContext|null $context
     */
    public function __construct( $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null )
    {
        Assert::stringNotEmpty( $body );
        Assert::allNotNull( [
            $typeResolver,
            $descriptionFactory
        ] );

        $this->tokens = preg_split( '/(\s+)/Su', $body, 3, PREG_SPLIT_DELIM_CAPTURE );

        $this->body = $body;
        $this->typeResolver = $typeResolver;
        $this->descriptionFactory = $descriptionFactory;
        $this->context = $context;
    }


    /**
     * @param string $token
     *
     * @return Type
     */
    private function parseType( $token )
    {
        $result = null;

        if ( $this->isValidToken( $token ) && ( $token[ 0 ] !== '$' ) )
        {
            $result = $this->typeResolver->resolve( $token, $this->context );
        }

        return $result;

    }

    /**
     * @param string $token
     *
     * @return string
     */
    private function parseVariableName( $token )
    {
        $result = $this->isValidToken( $token )
                ? ltrim( $token, '$')
                : '';

        return $result;

    }

    /**
     * @return MhProperty
     */
    public function parse()
    {
        $type = $this->parseType( $this->getToken() );
        $variableName = self::parseVariableName( $this->getToken() );

        $descriptionTokens = $this->implodeTokens();

        $description = $this->descriptionFactory->create( $descriptionTokens, $this->context );

        $result = new MhProperty(  $variableName, $type, $description );

        return $result;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private static function isValidToken( $token )
    {
        $result = !empty( $token );
        return $result;
    }

    /**
     *
     */
    private function getToken()
    {
        $result = false;

        while ( count( $this->tokens ) > 0 && empty( $result ) )
        {
            $result = trim( array_shift( $this->tokens ) );
        }

        return $result;
    }

    /**
     * @return string
     */
    private function implodeTokens()
    {

        $result = implode( '', $this->tokens );
        return $result;
    }

}
