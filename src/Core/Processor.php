<?php

namespace Tempa\Core;

use Tempa\Core\Exception\SubstituteException;
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

    /**
     * Processor constructor.
     *
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;

        $this->pattern = $this->buildPattern();
    }

    /**
     * Build a given pattern.
     * This is used to scan or replace a certain substitute
     *
     * @param string $name Substitute name to replace
     *
     * @return string
     */
    public function buildPattern($name = null)
    {
        $patternPrefix = '/' . preg_quote($this->options->prefix);
        $patternSuffix = preg_quote($this->options->suffix) . '/';

        return $patternPrefix . ($name ?: '(?<name>[^' . preg_quote($this->options->suffix) . ']+)') . $patternSuffix;
    }

    /**
     * @param \SplFileObject $file
     * @param array          $substitutionMap
     *
     * @return void
     */
    public function substitute(\SplFileObject $file, array $substitutionMap)
    {
        if (!in_array($file->getExtension(), $this->options->fileExtensions)) {
            return;
        }

        if (empty($substitutionMap)) {
            throw SubstituteException::emptyMapping();
        }

        $scanResult = $this->scan($file);

        $targetPath = rtrim($file->getPathname(), '.' . $file->getExtension());
        $fileContent = file_get_contents($file->getPathname());

        foreach ($scanResult as $substitute) {
            if (!isset($substitutionMap[$substitute->name])) {
                throw SubstituteException::missingSubstituteMapping($substitute->name);
            }

            $pattern = $this->buildPattern($substitute->name);
            $fileContent = preg_replace($pattern, $substitutionMap[$substitute->name], $fileContent);
        }

        file_put_contents($targetPath, $fileContent);
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
        if (!in_array($file->getExtension(), $this->options->fileExtensions)) {
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
}
