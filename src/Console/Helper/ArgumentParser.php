<?php

namespace Tempa\Console\Helper;

/**
 * Class ArgumentParser
 *
 * @package Tempa\Console\Helper
 * @author  icanhazstring <blubb0r05+github@gmail.com>
 */
class ArgumentParser
{

    private const FILE_JSON = 'json';
    private const FILE_PHP = 'php';

    public static array $supportedExtensions = [
        self::FILE_JSON,
        self::FILE_PHP
    ];

    /**
     * This will parse incoming values by splitting value by equal sign
     * and return an key=>value map
     *
     * @param array $input
     *
     * @return array
     */
    public static function parseMapping(array $input): array
    {
        $result = [];

        foreach ($input as $value) {
            $split = explode('=', $value, 2);

            // Simply avoid non valid mapping values (missing =)
            if (count($split) < 2) {
                continue;
            }

            $result[$split[0]] = $split[1];
        }

        return $result;
    }

    /**
     * Parse mapping file argument
     *
     * @param string $filePath
     *
     * @return array
     */
    public static function parseFile($filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("Mapping file not readable {$filePath}");
        }

        $fileInfo = new \SplFileInfo($filePath);
        if (!in_array($fileInfo->getExtension(), self::$supportedExtensions, true)) {
            throw new \InvalidArgumentException(
                'Mapping file not amongst the valid extensions ' . json_encode(
                    self::$supportedExtensions,
                    JSON_THROW_ON_ERROR
                )
            );
        }

        $map = [];

        switch ($fileInfo->getExtension()) {
            case 'json':
                $map = json_decode(file_get_contents($filePath), true, 512, JSON_THROW_ON_ERROR);
                break;

            case 'php':
                $map = require_once $filePath;
        }

        return $map;
    }
}
