<?php

namespace Alex19pov31\BitrixModel\Exceptions;


class ExceptionUpdateElementHlBlock extends \Exception
{
    protected $tableName;

    public function __construct(string $tableName, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->tableName = $tableName;
        parent::__construct($message, $code, $previous);
    }

    public function getTableName(): string
    {
        return (string)$this->tableName;
    }
}
