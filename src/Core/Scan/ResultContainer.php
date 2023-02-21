<?php

namespace Tempa\Core\Scan;

use ArrayAccess;
use Countable;
use Iterator;
use ReturnTypeWillChange;

/**
 * Class ScanResult
 *
 * @package Tempa\Core\Processor
 * @author  Andreas Frömer <andreas.froemer@check24.de>
 */
class ResultContainer implements ArrayAccess, Iterator, Countable
{

    /** @var Result[] */
    private array $items = [];
    private string $pathName;

    /**
     * ResultContainer constructor.
     *
     * @param string $pathName Path to file this result belongs to
     */
    public function __construct(string $pathName)
    {
        $this->pathName = $pathName;
    }

    /**
     * @return Result[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getPathName(): string
    {
        return $this->pathName;
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->items);
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        return next($this->items);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        return reset($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): Result
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $offset = $offset ?: count($this->items);
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
