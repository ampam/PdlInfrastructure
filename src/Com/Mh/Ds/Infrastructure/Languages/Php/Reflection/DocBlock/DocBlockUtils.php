<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/3/2017
 * Time: 2:50 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;

/**
 * Class DocBlockUtils
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection
 */
class DocBlockUtils
{

    const OPERATOR_NAMESPACE = '\\';

    /**
     * @var DocBlock[string]
     */
    private static $docBlockCache = [];

    private static $useStatements = [];

    private static $classNamespaces = [];

    /**
     * Factory method for easy instantiation.
     *
     * @param string[] $additionalTags
     *
     * @return DocBlockFactory
     */
    public static function createDockBlockFactory( array $additionalTags = [] )
    {
        $fqsenResolver = new FqsenResolver();

        $tagFactory = new PamStandardTagFactory( $fqsenResolver );
        $descriptionFactory = new DescriptionFactory( $tagFactory );

        $tagFactory->addService( $descriptionFactory );
        $tagFactory->addService( new TypeResolver( $fqsenResolver ) );

        $docBlockFactory = new DocBlockFactory( $descriptionFactory, $tagFactory );
        foreach ( $additionalTags as $tagName => $tagHandler )
        {
            $docBlockFactory->registerTagHandler( $tagName, $tagHandler );
        }

        return $docBlockFactory;
    }

    /**
     * @param $object
     * @param $propertyName
     *
     * @return Type
     */
    public static function getPropertyTypeAsString( $object, $propertyName )
    {
        /**
         * @var Type $result
         */
        $result = self::getPropertyType( $object, $propertyName )->__toString();
        return $result;
    }

    /**
     * @param $object
     *
     * @return Property[]
     */
    public static function getProperties( $object )
    {
        $docBlock = self::getDocBlock( $object );

        /** @var Property[] $result */
        $result = $docBlock->getTagsByName( 'property' );

        return $result;
    }

    /**
     * @param $object
     * @param $propertyName
     *
     * @return Type
     */
    public static function getPropertyType( $object, $propertyName )
    {
        //TODO if property not found we should check in the parent class of $object
        $docBlock = self::getDocBlock( $object );
        $property = self::getPropertyByName( $docBlock, $propertyName );

        $result = !empty( $property )
            ? $property->getType()
            : Unknown_::getInstance();

        if ( $result instanceof Object_ )
        {
            $result = self::fixPropertyType( $result, $object );
        }
        else if ( $result instanceof Array_ )
        {
            $result = self::fixArrayPropertyType( $result, $object );
        }

        return $result;
    }

    /**
     * @param $object
     *
     * @return DocBlock
     */
    private static function getDocBlock( $object )
    {
        $result = self::getDocBlockFromCache( $object );
        if ( $result == null )
        {
            $reflectedClass = new ReflectionClass( $object );
            $docBlock = $reflectedClass->getDocComment();
            $docBlockFactory = DocBlockUtils::createDockBlockFactory();
            if ( !empty( $docBlock ) )
            {
                $result = $docBlockFactory->create( $docBlock );
            }
            else
            {
                $result = $docBlockFactory->create( ' ' );
            }
            self::saveToDockBlockCache( $reflectedClass->getName(), $result );
        }

        return $result;
    }

    /**
     * @param DocBlock $docBlock
     * @param $propertyName
     *
     * @return Property
     */
    private static function getPropertyByName( DocBlock $docBlock, $propertyName )
    {
        $result = null;
        $properties = $docBlock->getTagsByName( 'property' );

        /** @var Property $property */
        foreach ( $properties as $property )
        {
            if ( $property->getVariableName() == $propertyName )
            {
                $result = $property;
                break;
            }
        }

        return $result;
    }

    /**
     * @param $object
     *
     * @return DocBlock
     */
    private static function getDocBlockFromCache( $object )
    {
        $reflectedClass = new ReflectionClass( $object );
        $className = $reflectedClass->getName();
        $result = isset( self::$docBlockCache[ $className ] )
            ? self::$docBlockCache[ $className ]
            : null;

        return $result;

    }

    /**
     * @param $className
     * @param $result
     */
    private static function saveToDockBlockCache( $className, $result )
    {
        self::$docBlockCache[ $className ] = $result;
    }

    /**
     * @param Object_ $type
     * @param $object
     *
     * @return Type
     *
     */
    public static function fixPropertyType( $type, $object )
    {
        $result = $type;
        $className = $type->__toString();
        if ( self::isInGlobalNamespace( $className ) )
        {
            $fullQualifiedClassName = self::tryCurrentNamespace( $className, $object );

            if ( empty( $fullQualifiedClassName ) )
            {
                $fullQualifiedClassName = self::tryUseStatements( $className, $object );
            }

            if ( !empty( $fullQualifiedClassName ) )
            {
                $result = new Object_( new Fqsen( $fullQualifiedClassName ) );
            }
        }

        if ( $result == null )
        {
            $result = $type;
        }

        return $result;
    }

    /**
     * @param Array_ $arrayType
     * @param $object
     *
     * @return Type
     *
     */
    public static function fixArrayPropertyType( $arrayType, $object )
    {
        $fullQualifiedClassName = null;
        $className = $arrayType->getValueType()->__toString();
        if ( self::isInGlobalNamespace( $className ) )
        {
            $fullQualifiedClassName = self::tryCurrentNamespace( $className, $object );

            if ( empty( $fullQualifiedClassName ) )
            {
                $fullQualifiedClassName = self::tryUseStatements( $className, $object );
            }
        }

        if ( !empty( $fullQualifiedClassName ) )
        {
            $valueType = new Object_( new Fqsen( $fullQualifiedClassName ) );
            $result =  new Array_( $valueType, $arrayType->getKeyType() );
        }
        else
        {
            $result = $arrayType;
        }



        return $result;
    }

    /**
     * @param $className
     *
     * @return String[][]
     */
    private static function getUseStatements( $className )
    {
        if ( !isset( self::$useStatements[ $className ] ) )
        {
            $extReflection = new ExtendedReflectionClass( $className );
            $result = $extReflection->getUseStatements();
            self::$useStatements[ $className ] = $result;
        }
        else
        {
            $result = self::$useStatements[ $className ];
        }

        return $result;
    }

    /**
     * @param $className
     *
     * @return string
     */
    private static function getClassNamespace( $className )
    {
        if ( !isset( self::$classNamespaces[ $className ] ) )
        {
            $namespaceParts = explode( self::OPERATOR_NAMESPACE, $className );

            array_pop( $namespaceParts );
            self::$classNamespaces[ $className ] = self::OPERATOR_NAMESPACE . implode( self::OPERATOR_NAMESPACE, $namespaceParts );

        }

        $result = self::$classNamespaces[ $className ];


        return $result;
    }

    /**
     * @param $className
     * @param $currentObject
     *
     * @return string
     */
    private static function tryCurrentNamespace( $className, $currentObject )
    {
        $result = null;

        $currentFullClassName = get_class( $currentObject );
        $currentNamespace = self::getClassNamespace( $currentFullClassName );

        $currentNamespaceWithClassName = "{$currentNamespace}{$className}";

        if ( class_exists( $currentNamespaceWithClassName ) )
        {
            $result = $currentNamespaceWithClassName;
        }

        return $result;

    }

    /**
     * @param $className
     * @param $currentObject
     *
     * @return string
     */
    private static function tryUseStatements( $className, $currentObject )
    {
        $result = null;
        $className = ltrim( $className, self::OPERATOR_NAMESPACE );
        $currentFullClassName = get_class( $currentObject );

        $useStatements = self::getUseStatements( $currentFullClassName );
        foreach ( $useStatements as $useStatement )
        {
            $fullClassName = self::OPERATOR_NAMESPACE . $useStatement[ 'class' ];
            if ( $useStatement[ 'as' ] == $className )
            {
                $result = $fullClassName;
                break;
            }
            else
            {
                $useClassParts = explode( self::OPERATOR_NAMESPACE, $fullClassName );
                $useClassName = end( $useClassParts );
                if ( $className == $useClassName )
                {
                    $result = $fullClassName;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param $className
     *
     * @return bool
     */
    protected static function isInGlobalNamespace( $className )
    {
        $result = substr( $className, 0, 1 ) == self::OPERATOR_NAMESPACE
            && substr_count( $className, self::OPERATOR_NAMESPACE ) === 1;
        return $result;
    }

}
