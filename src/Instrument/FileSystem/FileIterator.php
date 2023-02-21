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
    private string $rootDirectory;
    private array $fileEndings;

    /**
     * FileIterator constructor.
     *
     * @param string $rootDirectory  Path to root directory
     * @param array  $fileExtensions File endings settings
     */
    public function __construct(string $rootDirectory, array $fileExtensions)
    {
        $this->rootDirectory = $rootDirectory;
        $this->fileEndings = $fileExtensions;
    }

    /**
     * Walk previous set up root directory
     */
    public function iterate(): \CallbackFilterIterator
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

    public function getFilter(): callable
    {
        $fileEndings = $this->fileEndings;

        return static function (\SplFileInfo $file) use ($fileEndings) {
            if (!in_array($file->getExtension(), $fileEndings, true)) {
                return false;
            }

            return true;
        };
    }
}
