<?php

namespace Alex19pov31\BitrixModel;

use Alex19pov31\BitrixModel\Traits\Collection\CollectionTrait;
use Alex19pov31\BitrixModel\Traits\Collection\PaginationTrait;

class BaseModelCollection implements \ArrayAccess, \Iterator, \Countable
{
    use CollectionTrait;
    use PaginationTrait;

    protected $items = [];
    protected $class;
    protected $params;

    public function __construct(array $itemList, $class, array $params = [])
    {
        $this->class = $class;
        $this->params = $params;
        foreach ($itemList as $key => $item) {
            if ($item instanceof BaseModel) {
                $this->items[$key] = $item;
                continue;
            }

            $this->items[$key] = new $class($item);
        }
    }

    /**
     * @return BaseModel
     */
    protected function getClass()
    {
        return $this->class;
    }

    protected function getItems(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        $itemList = [];
        foreach ($this->items as $item) {
            $itemList[] = $item->toArray();
        }

        return $itemList;
    }

    /**
     * @return BaseModel|null
     */
    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        next($this->items);
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param [type] $offset
     * @return BaseModel|null
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
