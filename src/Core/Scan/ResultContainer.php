<?php

namespace Tempa\Core\Scan;

/**
 * Class ScanResult
 *
 * @package Tempa\Core\Processor
 * @author  Andreas Frömer <andreas.froemer@check24.de>
 */
class ResultContainer implements \ArrayAccess, \Iterator, \Countable
{

    /** @var Result[] */
    private $items = [];
    private $pathName;

    /**
     * ResultContainer constructor.
     *
     * @param string $pathName Path to file this result belongs to
     */
    public function __construct($pathName)
    {
        $this->pathName = $pathName;
    }

    /**
     * @return string
     */
    public function getPathName()
    {
        return $this->pathName;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        return next($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return key($this->items) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        return reset($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $offset = $offset ?: count($this->items);
        $this->items[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->items);
    }

}
