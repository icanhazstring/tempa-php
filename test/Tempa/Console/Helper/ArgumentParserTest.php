<?php

namespace Tempa\Test\Console\Helper;

use PHPUnit\Framework\TestCase;
use Tempa\Console\Helper\ArgumentParser;
use Vfs\FileSystem;
use Vfs\Node\File;

class ArgumentParserTest extends TestCase
{
    protected static FileSystem $fileSystem;
    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
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
    public static function tearDownAfterClass(): void
    {
        self::$fileSystem->unmount();
    }

    public function testEmptyInputShouldReturnEmptyResult(): void
    {
        self::assertEmpty(ArgumentParser::parseMapping([]));
    }

    public function testValidMappingShouldReturnKeyValueMap(): void
    {
        $result = ArgumentParser::parseMapping(['test=value']);

        self::assertArrayHasKey('test', $result);
        self::assertEquals('value', $result['test']);
    }

    public function testInvalidMappingShouldReturnEmptyResult(): void
    {
        $result = ArgumentParser::parseMapping(['awesomeTest']);

        self::assertEmpty($result);
    }

    public function testFileMappingWithUnreadableFileShouldRaiseAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ArgumentParser::parseFile('vfs://nope.json');
    }

    public function testFileMappingWithWrongExtensionsShouldRaiseAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ArgumentParser::parseFile('vfs://map.fubar');
    }

    public function testJsonFileMappingShouldReturnProperResult(): void
    {
        $result = ArgumentParser::parseFile('vfs://map.json');
        self::assertSame(['test' => 'map.json'], $result);
    }

    public function testPhpFileMappingShouldReturnProperResult(): void
    {
        $result = ArgumentParser::parseFile('vfs://map.php');
        self::assertSame(['test' => 'map.php'], $result);
    }
}
