<?php

namespace Tempa\Core;

use Tempa\Core\Scan\ResultContainer;
use Vfs\FileSystem;
use Vfs\Node\File;

/**
 * ProcessorTest
 *
 * @package Tempa\Core
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileSystem */
    protected static $fileSystem;
    protected static $defaultOptions;

    public static function setUpBeforeClass()
    {
        self::$fileSystem = FileSystem::factory('vfs://');
        self::$fileSystem->mount();

        self::$fileSystem->get('/')->add('invalidFileEnding.php', new File('{$test}'));
        self::$fileSystem->get('/')->add('noMatch.php.dist', new File('{test}'));
        self::$fileSystem->get('/')->add('match.php.dist', new File('Awesome {$test}'));

        self::$defaultOptions = new Options(['fileExtensions' => ['dist'], 'prefix' => '{$', 'suffix' => '}']);
    }

    public static function tearDownAfterClass()
    {
        self::$fileSystem->unmount();
    }

    public function testBuildPattern()
    {
        $processor = new Processor(self::$defaultOptions);

        self::assertEquals('/\{\$(?<name>[^\}]+)\}/', $processor->buildPattern());
        self::assertEquals('/\{\$test\}/', $processor->buildPattern('test'));
    }

    /**
     * Passing an invalid file to the processor should
     * return null. So nothing should happen.
     */
    public function testScanWithInvalidFileEnding_ShouldReturnNull()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileInfo = new \SplFileObject('vfs://invalidFileEnding.php');

        $result = $processor->scan($fileInfo);

        self::assertNull($result);
    }

    /**
     * Passing valid file and substitutions should return
     * a scan result with proper information about the file, substitution, position etc
     */
    public function testScanFileWithMatch_ShouldReturnResult()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $scanResult = $processor->scan($fileObject);

        self::assertInstanceOf(ResultContainer::class, $scanResult);
        self::assertCount(1, $scanResult);
        self::assertEquals($fileObject->getPathname(), $scanResult->getPathName());

        $first = $scanResult[0];

        self::assertEquals(0, $first->lineNumber);
        self::assertEquals('Awesome {$test}', $first->lineContent);
        self::assertEquals('test', $first->name);
    }

    /**
     * Matched file without proper substitution markers should return empty result
     */
    public function testScanFileWithoutMatch_ShouldReturnEmptyResult()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://noMatch.php.dist');

        $scanResult = $processor->scan($fileObject);

        self::assertInstanceOf(ResultContainer::class, $scanResult);
        self::assertEmpty($scanResult);
        self::assertEquals($fileObject->getPathname(), $scanResult->getPathName());
    }

    /**
     * Substitution should create a new file
     */
    public function testSubstituteWithMatch_ShouldCreateNewFile()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, ['test' => 'works!']);

        $target = self::$fileSystem->get('/match.php');
        self::assertNotNull($target);
        self::assertEquals('Awesome works!', $target->getContent());
    }

    /**
     * @depends testSubstituteWithMatch_ShouldCreateNewFile
     */
    public function testSubstituteWithMatchAgain_ShouldOverwriteFile()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, ['test' => 'works again!']);

        $target = self::$fileSystem->get('/match.php');
        self::assertNotNull($target);
        self::assertEquals('Awesome works again!', $target->getContent());
    }

    /**
     * Trying to replace a substiture should raise an exception
     * @expectedException \Tempa\Core\Exception\SubstituteException
     */
    public function testSubstituteWithoutMatch_ShouldThrowException()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, []);
    }
}
