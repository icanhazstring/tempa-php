<?php

namespace Tempa\Console\Helper;

class ArgumentParser
{

    /**
     * This will parse incoming values by splitting value by equal sign
     * and return an key=>value map
     *
     * @param array $input
     *
     * @return array
     */
    public static function parseMapping(array $input)
    {
        $result = [];

        foreach ($input as $value) {
            $split = explode('=', $value, 2);

            // Simply avoid non valid mapping values (missing =)
            if (count($split) < 2) {
                continue;
            }

            $result[$split[0]] = $split[1];
        }

        return $result;
    }
}
