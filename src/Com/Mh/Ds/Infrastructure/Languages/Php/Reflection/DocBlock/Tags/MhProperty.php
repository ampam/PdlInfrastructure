<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/3/2017
 * Time: 2:53 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\Tags;

use Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\PropertyParser;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;

/**
 * Class PamProperty
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock\Tags
 */
class MhProperty extends TagWithType
//class MhProperty extends TagWithType implements StaticMethod
{

    /** @var string */
    protected $variableName = '';

    public function __construct(?string $variableName, ?Type $type = null, ?Description $description = null)
    {
        Assert::string($variableName);

        $this->name         = 'property';
        $this->variableName = $variableName;
        $this->type         = $type;
        $this->description  = $description;
    }

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

    /**
     * Returns a string representation for this tag.
     */
    public function __toString() : string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        if ($this->variableName) {
            $variableName = '$' . $this->variableName;
        } else {
            $variableName = '';
        }

        $type = (string) $this->type;

        return $type
            . ($variableName !== '' ? ($type !== '' ? ' ' : '') . $variableName : '')
            . ($description !== '' ? ($type !== '' || $variableName !== '' ? ' ' : '') . $description : '');
    }


    /**
     * Returns the variable's name.
     *
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }


}
