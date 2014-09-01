<?php

namespace Clerk\Parser;

class IOException extends \RuntimeException implements Exception
{
    use ExceptionTrait;

    public function __construct($message, $line, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->setSourceLine($this);
    }
}
