<?php

namespace Tempa\Core;

use Tempa\Core\Scan\Result;
use Tempa\Core\Scan\ResultContainer;

/**
 * Processor
 *
 * This will scan or substitute given files
 *
 * @package Tempa\Core
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class Processor
{

    private $options;
    private $pattern;

    public function __construct(Options $options)
    {
        $this->options = $options;

        $this->pattern =
            '/'
            . preg_quote($this->options->prefix)
            . '(?<name>[^'
            . preg_quote($this->options->suffix)
            . ']+)'
            . preg_quote($this->options->suffix)
            . '/';
    }

    /**
     * Scan a given file for substitutes
     *
     * @param \SplFileObject $file
     *
     * @return null|ResultContainer
     */
    public function scan(\SplFileObject $file)
    {
        if (!in_array($file->getExtension(), $this->options->fileEndings)) {
            return null;
        }

        $result = new ResultContainer($file->getPathname());

        while (!$file->eof()) {
            $line = trim($file->fgets());

            if (preg_match($this->pattern, $line, $match)) {
                $result[] = new Result([
                    'name'        => $match['name'],
                    'lineNumber'  => $file->key(),
                    'lineContent' => $line
                ]);
            }
        }

        return $result;
    }

    public function substitute(ResultContainer $resultContainer)
    {
    }
}
