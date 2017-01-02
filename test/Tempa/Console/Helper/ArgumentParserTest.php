<?php

namespace Tempa\Console\Helper;

class ArgumentParserTest extends \PHPUnit_Framework_TestCase
{
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
}
