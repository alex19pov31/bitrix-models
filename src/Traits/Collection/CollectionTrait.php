<?php

namespace Alex19pov31\BitrixModel\Traits\Collection;

trait CollectionTrait
{
    /**
     * @return BaseModel
     */
    abstract protected function getClass();
    abstract protected function getItems(): array;

    public function merge($data): self
    {
        return new static([], $this->getClass());
    }

    public function where($column, $value): self
    {
        $newItems = array_filter($this->getItems(), function ($item) use ($column, $value) {
            return $item[$column] == $value;
        });

        return new static($newItems, $this->getClass());
    }

    public function whereCallback(callable $filterFunc): self
    {
        $newItems = array_filter($this->getItems(), $filterFunc($item));
        return new static($newItems, $this->getClass());
    }

    public function sort(string $column, bool $isAscending = true): self
    {
        $newList = $this->getItems();
        usort($newList, function ($a, $b) use ($column, $isAscending) {
            $columnA = $a[$column];
            $columnB = $b[$column];
            return $this->internalSort($columnA, $columnB, $isAscending);
        });

        return new static($newList ? $newList : [], $this->getClass());
    }

    private function internalSort($columnA, $columnB, $isAscending)
    {
        $num = $isAscending ? 1 : -1;

        if (is_numeric($columnA) && is_numeric($columnB)) {
            return $num * ((float) $columnA - (float) $columnB);
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

    public function select(array $columnList): self
    {
        $newList = [];
        foreach ($this->getItems() as $key => $item) {
            foreach ($columnList as $column) {
                $newList[$key][$column] = $item[$column] ? $item[$column] : null;
            }
        }

        return new static($newList, $this->getClass());
    }

    public function keyBy(string $column): self
    {
        $newList = [];
        foreach ($this->getItems() as $item) {
            $key = $item[$column];
            if ($key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $item;
        }

        return new static($newList, $this->getClass());
    }

    public function column(string $column): array
    {
        $result = [];
        foreach ($this->getItems() as $item) {
            $result[] = $item[$column];
        }

        return $result;
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $newList = [];
        $i = 0;
        foreach ($this->getItems() as $key => $item) {
            if ($offset > $i++) {
                continue;
            }
            if (!$limit--) {
                break;
            }

            $newList[$key] = $item;
        }

        return new static($newList, $this->getClass());
    }

    /**
     * @return BaseModel|null
     */
    public function first()
    {
        foreach ($this->getItems() as $item) {
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
        foreach ($this->getItems() as $item) {
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
        foreach ($this->getItems() as $key => $item) {
            $groupKey = $item[$column];
            $newList[$groupKey][$key] = $item;
        }

        $collectionList = [];
        foreach ($newList as $groupKey => $group) {
            $collectionList[$groupKey] = new static($group, $this->getClass());
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
        foreach ($this->getItems() as $item) {
            $sum += (float) $item[$column];
        }

        return (float) $sum;
    }

    public function mapToArray(callable $calc, $keyBy = null): array
    {
        $newList = [];
        foreach ($this->getItems() as $item) {
            $key = $item[$keyBy];
            if ($keyBy && $key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $calc($item);
        }

        return $newList;
    }

    public function map(callable $calc, $keyBy = null): self
    {
        $newList = [];
        foreach ($this->getItems() as $item) {
            $key = $item[$keyBy];
            if ($keyBy && $key) {
                $newList[$key] = $item;
                continue;
            }

            $newList[] = $calc($item);
        }

        return new static($newList, $this->getClass());
    }

    public function addField(string $fieldName, callable $calc)
    {
        $newList = [];
        foreach ($this->getItems() as $key => $item) {
            $item[$fieldName] = $calc($item);
            $newList[$key] = $item;
        }

        return new static($newList, $this->getClass());
    }

    public function multiSort(array $sortList)
    {
        $newList = $this->getItems();
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

        return new static($newList ? $newList : [], $this->getClass());
    }
}