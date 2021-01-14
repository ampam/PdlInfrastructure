<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 11/6/2018
 * Time: 8:09 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock;


use ReflectionClass;
use RuntimeException;

/**
 * Class ExtendedReflectionClass
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock
 */
class ExtendedReflectionClass extends ReflectionClass
{


    /**
     * Array of use statements for class.
     *
     * @var array
     */
    protected $useStatements = [];


    /**
     * Check if use statements have been parsed.
     *
     * @var boolean
     */
    protected $useStatementsParsed = false;


    /**
     * Parse class file and get use statements from current namespace.
     *
     */
    protected function parseUseStatements()
    {

        if ( $this->useStatementsParsed )
        {
            return $this->useStatements;
        }

        if ( !$this->isUserDefined() )
        {
            throw new RuntimeException( 'Must parse use statements from user defined classes.' );
        }

        $source = $this->readFileSource();
        $this->useStatements = $this->tokenizeSource( $source );

        $this->useStatementsParsed = true;
        return $this->useStatements;
    }


    /**
     * Read file source up to the line where our class is defined.
     *
     * @return string
     */
    private function readFileSource()
    {

        $file = fopen( $this->getFileName(), 'r' );
        $line = 0;
        $source = '';

        while ( !feof( $file ) )
        {
            ++$line;

            if ( $line >= $this->getStartLine() )
            {
                break;
            }

            $source .= fgets( $file );
        }

        fclose( $file );

        return $source;
    }


    /**
     * Parse the use statements from read source by
     * tokenizing and reading the tokens. Returns
     * an array of use statements and aliases.
     *
     * @param string $source
     *
     * @return array
     */
    private function tokenizeSource( $source )
    {

        $tokens = token_get_all( $source );

        $builtNamespace = '';
        $buildingNamespace = false;
        $matchedNamespace = false;

        $useStatements = [];
        $record = false;
        $currentUse = [
            'class' => '',
            'as' => ''
        ];

        foreach ( $tokens as $token )
        {

            if ( $token[ 0 ] === T_NAMESPACE )
            {
                $buildingNamespace = true;

                if ( $matchedNamespace )
                {
                    break;
                }
            }

            if ( $buildingNamespace )
            {

                if ( $token === ';' )
                {
                    $buildingNamespace = false;
                    continue;
                }

                switch ( $token[ 0 ] )
                {

                    case T_STRING:
                    case T_NS_SEPARATOR:
                        $builtNamespace .= $token[ 1 ];
                        break;
                }

                continue;
            }

            if ( $token === ';' || !is_array( $token ) )
            {

                if ( $record )
                {
                    $useStatements[] = $currentUse;
                    $record = false;
                    $currentUse = [
                        'class' => '',
                        'as' => ''
                    ];
                }

                continue;
            }

            if ( $token[ 0 ] === T_CLASS )
            {
                break;
            }

            if ( strcasecmp( $builtNamespace, $this->getNamespaceName() ) === 0 )
            {
                $matchedNamespace = true;
            }

            if ( $matchedNamespace )
            {

                if ( $token[ 0 ] === T_USE )
                {
                    $record = 'class';
                }

                if ( $token[ 0 ] === T_AS )
                {
                    $record = 'as';
                }

                if ( $record )
                {
                    switch ( $token[ 0 ] )
                    {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $currentUse[ $record ] .= $token[ 1 ];
                    }
                }
            }

            if ( $token[ 2 ] >= $this->getStartLine() )
            {
                break;
            }
        }


        // Make sure the as key has the name of the class even
        // if there is no alias in the use statement.
        foreach ( $useStatements as &$useStatement )
        {

            if ( empty( $useStatement[ 'as' ] ) )
            {

                $useStatement[ 'as' ] = basename( $useStatement[ 'class' ] );
            }
        }

        return $useStatements;
    }


    /**
     * Return array of use statements from class.
     *
     * @return array
     */
    public function getUseStatements()
    {

        return $this->parseUseStatements();
    }


    /**
     * Check if class is using a class or an alias of a class.
     *
     * @param string $class
     *
     * @return boolean
     */
    public function hasUseStatement( $class )
    {

        $useStatements = $this->parseUseStatements();

        return
            array_search( $class, array_column( $useStatements, 'class' ) ) ||
            array_search( $class, array_column( $useStatements, 'as' ) );
    }
}