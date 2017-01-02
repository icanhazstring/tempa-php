<?php

namespace Tempa\Core\Exception;

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
    public static function missingSubstituteMapping($name)
    {
        return new self("Missing substitute mapping for {$name}");
    }
}
