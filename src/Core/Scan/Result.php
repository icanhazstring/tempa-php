<?php

namespace Tempa\Core\Scan;

/**
 * Class Result
 *
 * @package Tempa\Core\Scan
 * @author  Andreas Frömer <andreas.froemer@check24.de>
 *
 * @property string name        Name of the substitute
 * @property string file        File path where substitute was found
 * @property int    lineNumber  Line number the substitue was found
 * @property string lineContent Preview of the line the substitute was found
 */
class Result extends \ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function __construct($input = [], $flags = self::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }
}
