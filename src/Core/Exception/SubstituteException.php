<?php

namespace Tempa\Core\Exception;

/**
 * Class SubstituteException
 *
 * @package Tempa\Core\Exception
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class SubstituteException extends \Exception
{

    /**
     * Create new exception when trying to replace a substitute where
     * not definition was given.
     *
     * @param string $name
     *
     * @return SubstituteException
     */
    public static function missingSubstituteMapping($name): SubstituteException
    {
        return new self("Missing substitute mapping for {$name}");
    }

    /**
     * Create new exception if substitution map is empty.
     *
     * @return SubstituteException
     */
    public static function emptyMapping(): SubstituteException
    {
        return new self('Substitution map is empty');
    }
}
