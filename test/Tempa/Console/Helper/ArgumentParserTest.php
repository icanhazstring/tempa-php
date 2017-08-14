<?php

namespace Tempa\Test\Console\Helper;

use PHPUnit\Framework\TestCase;
use Tempa\Console\Helper\ArgumentParser;
use Vfs\FileSystem;
use Vfs\Node\File;

class ArgumentParserTest extends TestCase
{
    /** @var FileSystem */
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

    public function testEmptyInputShouldReturnEmptyResult()
    {
        self::assertEmpty(ArgumentParser::parseMapping([]));
    }

    public function testValidMappingShouldReturnKeyValueMap()
    {
        $result = ArgumentParser::parseMapping(['test=value']);

        self::assertArrayHasKey('test', $result);
        self::assertEquals('value', $result['test']);
    }

    public function testInvalidMappingShouldReturnEmptyResult()
    {
        $result = ArgumentParser::parseMapping(['awesomeTest']);

        self::assertEmpty($result);
    }

    public function testFileMappingWithUnreadableFileShouldRaiseAnException()
    {
        $this->expectException(\InvalidArgumentException::class);
        ArgumentParser::parseFile('vfs://nope.json');
    }

    public function testFileMappingWithWrongExtensionsShouldRaiseAnException()
    {
        $this->expectException(\InvalidArgumentException::class);
        ArgumentParser::parseFile('vfs://map.fubar');
    }

    public function testJsonFileMappingShouldReturnProperResult()
    {
        $result = ArgumentParser::parseFile('vfs://map.json');
        self::assertSame(['test' => 'map.json'], $result);
    }

    public function testPhpFileMappingShouldReturnProperResult()
    {
        $result = ArgumentParser::parseFile('vfs://map.php');
        self::assertSame(['test' => 'map.php'], $result);
    }
}
