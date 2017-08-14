<?php

namespace Tempa\Test\Core;

use Tempa\Core\Options;
use Tempa\Core\Processor;
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
        self::$fileSystem->get('/')->add('doubleTokenMatch.php.dist', new File('{$test} {$test2}'));
        self::$fileSystem->get('/')->add('match.php.dist', new File('Awesome {$test}'));
        self::$fileSystem->get('/')->add('matchDuplicate.php.dist', new File(
            'Awesome {$test}' . PHP_EOL
            . '{$test}'
        ));

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
    public function testScanWithInvalidFileEndingShouldReturnNull()
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
    public function testScanFileWithMatchShouldReturnResult()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $scanResult = $processor->scan($fileObject);

        self::assertInstanceOf(ResultContainer::class, $scanResult);
        self::assertCount(1, $scanResult);
        self::assertEquals($fileObject->getPathname(), $scanResult->getPathName());
        self::assertArrayHasKey('test', $scanResult);

        $first = $scanResult['test'];

        self::assertCount(1, $scanResult);
        self::assertEquals(0, $first->lineNumber);
        self::assertEquals('Awesome {$test}', $first->lineContent);
        self::assertEquals('test', $first->name);
    }

    public function testScanFileWithMatchDuplicateShouldReturnResult()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://matchDuplicate.php.dist');

        $scanResult = $processor->scan($fileObject);

        self::assertCount(1, $scanResult);
    }

    /**
     * Matched file without proper substitution markers should return empty result
     */
    public function testScanFileWithoutMatchShouldReturnEmptyResult()
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
    public function testSubstituteWithMatchShouldCreateNewFile()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, ['test' => 'works!']);

        $target = self::$fileSystem->get('/match.php');
        self::assertNotNull($target);
        self::assertEquals('Awesome works!', $target->getContent());
    }

    /**
     * @depends testSubstituteWithMatchShouldCreateNewFile
     */
    public function testSubstituteWithMatchAgainShouldOverwriteFile()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, ['test' => 'works again!']);

        $target = self::$fileSystem->get('/match.php');
        self::assertNotNull($target);
        self::assertEquals('Awesome works again!', $target->getContent());
    }

    /**
     * Trying to replace a substiture with empty mapping should raise an exception
     *
     * @expectedException \Tempa\Core\Exception\SubstituteException
     */
    public function testSubstituteEmptyMappingShouldThrowException()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, []);
    }

    /**
     * Trying to replace a substiture with missing mapping should raise an exception
     *
     * @expectedException \Tempa\Core\Exception\SubstituteException
     */
    public function testSubstituteWithoutMatchShouldThrowException()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://match.php.dist');

        $processor->substitute($fileObject, ['a' => 'b']);
    }

    public function testMultipleTokensOnSingleLine()
    {
        $processor = new Processor(self::$defaultOptions);
        $fileObject = new \SplFileObject('vfs://doubleTokenMatch.php.dist');

        $processor->substitute($fileObject, ['test' => 'Hello', 'test2' => 'World']);

        $target = self::$fileSystem->get('/doubleTokenMatch.php');
        self::assertNotNull($target);
        self::assertEquals('Hello World', $target->getContent());
    }
}
