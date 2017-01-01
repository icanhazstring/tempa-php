<?php


namespace Tempa\Instrument\FileSystem;


use Tempa\Core\Options;

class FileIterator
{
    private $rootDirectory;
    private $options;

    /**
     * FileIterator constructor.
     *
     * @param string  $rootDirectory Path to root directory
     * @param Options $options       Options array
     */
    public function __construct($rootDirectory, Options $options)
    {
        $this->rootDirectory = $rootDirectory;
        $this->options = $options;
    }

    /**
     * Walk previous set up root directory
     *
     * @return \CallbackFilterIterator
     */
    public function walk()
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
        $options = $this->options;

        return function (\SplFileInfo $file) use ($rootDirectory, $options) {

            if (!in_array($file->getExtension(), $options->fileEndings)) {
                return false;
            }

            return true;

        };
    }

}