<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/3/2017
 * Time: 2:53 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\Tags;

use Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\PropertyParser;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;

/**
 * Class PamProperty
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\Tags
 */
class MhProperty extends Property
{

    /**
     * {@inheritdoc}
     */
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    )
    {
        $parser = new PropertyParser( $body, $typeResolver, $descriptionFactory, $context );
        $result = $parser->parse();
        return $result;
    }


}
