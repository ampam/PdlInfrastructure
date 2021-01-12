<?php
/**
 * Created by PhpStorm.
 * User: AM
 * Date: 10/3/2017
 * Time: 4:39 PM
 */

namespace Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock;


use Com\Mh\Ds\Infrastructure\Container\SingletonTrait;
use phpDocumentor\Reflection\Type;

/**
 * Class Unknown_
 * @package Com\Mh\Ds\Infrastructure\Languages\Php\Reflection\DocBlock8
 */
final class Unknown_ implements Type
{
    use SingletonTrait;

    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'unknown';
    }
}
