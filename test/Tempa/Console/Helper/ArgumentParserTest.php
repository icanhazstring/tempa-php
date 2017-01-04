<?php

namespace Tempa\Console\Helper;

use Vfs\FileSystem;
use Vfs\Node\File;

class ArgumentParserTest extends \PHPUnit_Framework_TestCase
{
    protected static $fileSystem;
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        self::$fileSystem = FileSystem::factory('vfs://');
        self::$fileSystem->mount();

        self::$fileSystem->get('/')->add('map.json', new File('{"test": "map.json"}'));
        self::$fileSystem->get('/')->add('map.php', new File('<?php return ["test" => "map.php"];'));
        self::$fileSystem->get('/')->add('map.fubar', new File());
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass()
    {
        self::$fileSystem->unmount();
    }

    public function testEmptyInput_ShouldReturnEmptyResult()
    {
        self::assertEmpty(ArgumentParser::parseMapping([]));
    }

    public function testValidMapping_ShouldReturnKeyValueMap()
    {
        $result = ArgumentParser::parseMapping(['test=value']);

        self::assertArrayHasKey('test', $result);
        self::assertEquals('value', $result['test']);
    }

    public function testInvalidMapping_ShouldReturnEmptyResult()
    {
        $result = ArgumentParser::parseMapping(['awesomeTest']);

        self::assertEmpty($result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileMappingWithUnreadableFile_ShouldRaiseAnException()
    {
        ArgumentParser::parseFile('vfs://nope.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFileMappingWithWrongExtensions_ShouldRaiseAnException()
    {
        ArgumentParser::parseFile('vfs://map.fubar');
    }

    public function testJsonFileMapping_ShouldReturnProperResult()
    {
        $result = ArgumentParser::parseFile('vfs://map.json');
        self::assertSame(['test' => 'map.json'], $result);
    }

    public function testPhpFileMapping_ShouldReturnProperResult()
    {
        $result = ArgumentParser::parseFile('vfs://map.php');
        self::assertSame(['test' => 'map.php'], $result);
    }
}
