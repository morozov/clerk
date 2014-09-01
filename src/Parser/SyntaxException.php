<?php

namespace Clerk\Parser;

class SyntaxException extends \LogicException implements Exception
{
    use ExceptionTrait;

    public function __construct($message, $sourceLine, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->setSourceLine($sourceLine);
    }
}
