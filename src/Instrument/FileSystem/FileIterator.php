<?php

namespace Tempa\Instrument\FileSystem;

use Tempa\Core\Options;

/**
 * FileIterator
 *
 * This is used to filter out template files
 * from a given root directory.
 *
 * @package Tempa\Instrument\FileSystem
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class FileIterator
{
    private $rootDirectory;
    private $fileEndings;

    /**
     * FileIterator constructor.
     *
     * @param string $rootDirectory Path to root directory
     * @param array  $fileEndings   File endings settings
     */
    public function __construct($rootDirectory, array $fileEndings)
    {
        $this->rootDirectory = $rootDirectory;
        $this->fileEndings = $fileEndings;
    }

    /**
     * Walk previous set up root directory
     *
     * @return \CallbackFilterIterator
     */
    public function iterate()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->rootDirectory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $callback = $this->getFilter();

        return new \CallbackFilterIterator($iterator, $callback);
    }

    public function getFilter()
    {
        $rootDirectory = $this->rootDirectory;
        $fileEndings = $this->fileEndings;

        return function (\SplFileInfo $file) use ($rootDirectory, $fileEndings) {

            if (!in_array($file->getExtension(), $fileEndings)) {
                return false;
            }

            return true;

        };
    }

}
