<?php

namespace Tempa\Core;

/**
 * Options
 *
 * Container for holding options like placeholder suffix prefix and other stuff
 *
 * @package Core
 * @author  icanhazstring <blubb0r05@gmail.com>
 *
 * @property string prefix Placeholder prefix
 * @property string suffix Placeholder suffix
 * @property array fileEndings Array containing all valid file endings
 */
class Options extends \ArrayObject
{

    /**
     * {@inheritdoc}
     */
    public function __construct($input = null, $flags = self::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }

}