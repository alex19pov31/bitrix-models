<?php

namespace Alex19pov31\BitrixModel\Exceptions;


class ExceptionAddElementIblock extends \Exception
{
    protected $iblockId;

    public function __construct(int $iblockId, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->iblockId = $iblockId;
        parent::__construct($message, $code, $previous);
    }

    public function getIblockId(): int
    {
        return (int)$this->iblockId;
    }
}
