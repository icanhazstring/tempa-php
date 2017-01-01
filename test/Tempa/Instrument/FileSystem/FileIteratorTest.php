<?php

namespace Tempa\Instrument\FileSystem;

use Tempa\Core\Options;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

class FileIteratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileSystem */
    protected static $fileSystem;

    public static function setUpBeforeClass()
    {
        self::$fileSystem = FileSystem::factory('vfs://');
        self::$fileSystem->mount();
    }

    public static function tearDownAfterClass()
    {
        self::$fileSystem->unmount();
    }

    /**
     * Passing an empty directory to the FileIterator should
     * simply return an empty result.
     */
    public function testEmptyDirectory_ShouldReturnEmptyResult()
    {
        $iterator = new FileIterator('vfs://', new Options([]));
        self::assertEmpty(iterator_to_array($iterator->walk()));
    }

    /**
     * Create a single file with property file ending
     */
    public function testSingleFileWithProperFileEnding_ShouldReturnArrayWithCorrectPath()
    {
        self::$fileSystem->get('/')->add('test.php.dist', new File());

        $iterator = new FileIterator('vfs://', new Options([
            'fileEndings' => ['dist']
        ]));

        $result = iterator_to_array($iterator->walk());
        self::assertNotEmpty($result);
        self::assertArrayHasKey('vfs://test.php.dist', $result);

        return 'vfs://test.php.dist';
    }

    /**
     * @depends testSingleFileWithProperFileEnding_ShouldReturnArrayWithCorrectPath
     */
    public function testMultipleFilesRecursive_ShouldReturnArrayWithCorrectPaths($previousResult)
    {
        self::$fileSystem->get('/')->add('test2.php.skel', new File());

        $iterator = new FileIterator('vfs://', new Options([
            'fileEndings' => ['dist', 'skel']
        ]));
        $result = iterator_to_array($iterator->walk());

        self::assertCount(2, $result);
        self::assertArrayHasKey($previousResult, $result);
        self::assertArrayHasKey('vfs://test2.php.skel', $result);
    }
}
