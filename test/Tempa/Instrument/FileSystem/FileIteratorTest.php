<?php

namespace Tempa\Test\Instrument\FileSystem;

use PHPUnit\Framework\TestCase;
use Tempa\Instrument\FileSystem\FileIterator;
use Vfs\FileSystem;
use Vfs\Node\Directory;
use Vfs\Node\File;

/**
 * FileIteratorTest
 *
 * @package Tempa\Instrument\FileSystem
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class FileIteratorTest extends TestCase
{

    /** @var FileSystem */
    protected static $fileSystem;

    public static function setUpBeforeClass(): void
    {
        self::$fileSystem = FileSystem::factory('vfs://');
        self::$fileSystem->mount();
    }

    public static function tearDownAfterClass(): void
    {
        self::$fileSystem->unmount();
    }

    /**
     * Passing an empty directory to the FileIterator should
     * simply return an empty result.
     */
    public function testEmptyDirectoryShouldReturnEmptyResult()
    {
        $iterator = new FileIterator('vfs://', []);
        self::assertEmpty(iterator_to_array($iterator->iterate()));
    }

    /**
     * Create a single file with property file ending
     */
    public function testSingleFileWithProperFileEndingShouldReturnArrayWithCorrectPath()
    {
        self::$fileSystem->get('/')->add('test.php.dist', new File());

        $iterator = new FileIterator('vfs://', ['dist']);

        $result = iterator_to_array($iterator->iterate());
        self::assertNotEmpty($result);
        self::assertArrayHasKey('vfs://test.php.dist', $result);

        return 'vfs://test.php.dist';
    }

    /**
     * Multiple file with different endings should be
     * in iterated result as well.
     *
     * @param $previousResult
     *
     * @depends testSingleFileWithProperFileEndingShouldReturnArrayWithCorrectPath
     * @return array
     */
    public function testMultipleFilesRecursiveShouldReturnArrayWithCorrectPaths($previousResult)
    {
        self::$fileSystem->get('/')->add('test2.php.skel', new File());

        $iterator = new FileIterator('vfs://', ['dist', 'skel']);
        $result = iterator_to_array($iterator->iterate());

        self::assertCount(2, $result);
        self::assertArrayHasKey('vfs://test2.php.skel', $result);

        return [
            $previousResult,
            'vfs://test2.php.skel'
        ];
    }

    /**
     * @depends testMultipleFilesRecursiveShouldReturnArrayWithCorrectPaths
     */
    public function testRecursiveFilesShouldReturnArrayWithCorrectPaths()
    {
        $dir = new Directory(['test3.php.dist' => new File()]);
        self::$fileSystem->get('/')->add('sub', $dir);

        $iterator = new FileIterator('vfs://', ['dist', 'skel']);
        $result = iterator_to_array($iterator->iterate());

        self::assertCount(3, $result);
        self::assertArrayHasKey('vfs://sub/test3.php.dist', $result);
    }
}
