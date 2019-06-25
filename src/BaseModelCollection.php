<?php
namespace Alex19pov31\BitrixModel;

class BaseModelCollection implements \ArrayAccess, \Iterator, \Countable
{
    private $items = [];
    private $class;

    public function __construct(array $itemList, $class)
    {
        $this->class = $class;
        foreach ($itemList as $key => $item) {
            if ($item instanceof BaseModel) {
                $this->items[$key] = $item;
                continue;
            }

            $this->items[$key] = new $class($item);
        }
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

    public function merge($data): BaseModelCollection
    {
        return new static([], $this->class);
    }

    public function where($column, $value): BaseModelCollection
    {
        $newItems = array_filter($this->items, function ($item) use ($column, $value) {
            return $item[$column] == $value;
        });

        return new static($newItems, $this->class);
    }

    public function whereCallback(callable $filterFunc): BaseModelCollection
    {
        $newItems = array_filter($this->items, $filterFunc($item));
        return new static($newItems, $this->class);
    }

    public function sort(string $column, bool $isAscending = true): BaseModelCollection
    {
        $newList = $this->items;
        usort($newList, function ($a, $b) use ($column, $isAscending) {
            $columnA = $a[$column];
            $columnB = $b[$column];
            return $this->internalSort($columnA, $columnB, $isAscending);
        });

        return new static($newList ? $newList : [], $this->class);
    }

    private function internalSort($columnA, $columnB, $isAscending)
    {
        $num = $isAscending ? 1 : -1;

        if (is_numeric($columnA) && is_numeric($columnB)) {
            return $num * ((float)$columnA - (float)$columnB);
        }

        if (is_numeric($columnA) && !is_numeric($columnB)) {
            return $num * -1;
        }

        if (!is_numeric($columnA) && is_numeric($columnB)) {
            return $num * 1;
        }

        if (!is_numeric($columnA) && !is_numeric($columnB)) {
            return $num * strcmp($columnA, $columnB);
        }
    }

    public function select(array $columnList): BaseModelCollection
    {
        $newList = [];
        foreach ($this->items as $key => $item) {
            foreach ($columnList as $column) {
                $newList[$key][$column] = $item[$column] ? $item[$column] : null;
            }
        }

        return new static($newList, $this->class);
    }

    public function keyBy(string $column): BaseModelCollection
    {
        $newList = [];
        foreach ($this->items as $item) {
            $key = $item[$column];
            if ($key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $item;
        }

        return new static($newList, $this->class);
    }

    public function column(string $column): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item[$column]) {
                $result[] = $item[$column];
            }
        }

        return $result;
    }

    public function limit(int $limit, int $offset = 0): BaseModelCollection
    {
        $newList = [];
        $i = 0;
        foreach ($this->items as $key => $item) {
            if ($offset > $i++) {
                continue;
            }
            if (!$limit--) {
                break;
            }

            $newList[$key] = $item;
        }

        return new static($newList, $this->class);
    }

    /**
     * @return BaseModel|null
     */
    public function first()
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    /**
     * @return BaseModel|null
     */
    public function last()
    {
        $lastItem = null;
        foreach ($this->items as $item) {
            $lastItem = $item;
        }
        return $lastItem;
    }

    /**
     * @param string $column
     * @return BaseModel|null
     */
    public function min(string $column)
    {
        return $this->sort($column)->first();
    }

    /**
     * @param string $column
     * @return array
     */
    public function groupBy(string $column): array
    {
        $newList = [];
        foreach ($this->items as $key => $item) {
            $groupKey = $item[$column];
            $newList[$groupKey][$key] = $item;
        }

        $collectionList = [];
        foreach ($newList as $groupKey => $group) {
            $collectionList[$groupKey] = new static($group, $this->class);
        }

        return $collectionList;
    }

    /**
     * @param string $column
     * @return BaseModel|null
     */
    public function max(string $column)
    {
        return $this->sort($column, false)->first();
    }

    /**
     * @param string $column
     * @return float
     */
    public function sum(string $column): float
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += (float)$item[$column];
        }

        return (float)$sum;
    }

    public function mapToArray(callable $calc, $keyBy = null): array
    {
        $newList = [];
        foreach ($this->items as $item) {
            $key = $item[$keyBy];
            if ($keyBy && $key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $calc($item);
        }

        return $newList;
    }

    public function map(callable $calc, $keyBy = null): BaseModelCollection
    {
        $newList = [];
        foreach ($this->items as $item) {
            $key = $item[$keyBy];
            if ($keyBy && $key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $calc($item);
        }

        return new static($newList, $this->class);
    }

    public function addField(string $fieldName, callable $calc)
    {
        $newList = [];
        foreach ($this->items as $key => $item) {
            $item[$fieldName] = $calc($item);
            $newList[$key] = $item;
        }

        return new static($newList, $this->class);
    }

    public function multiSort(array $sortList)
    {
        $newList = $this->items;
        usort($newList, function ($a, $b) use ($sortList) {
            foreach ($sortList as $column => $sort) {
                $columnA = $a[$column];
                $columnB = $b[$column];
                $isAscending = in_array($sort, ['asc', 'ASC']);
                $result = $this->internalSort($columnA, $columnB, $isAscending);
                if ($result != 0) {
                    return $result;
                }
            }
        });

        return new static($newList ? $newList : [], $this->class);
    }
}
