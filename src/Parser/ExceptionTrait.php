<?php

namespace Clerk\Parser;

trait ExceptionTrait
{
    protected $sourceLine;

    public function getSourceLine()
    {
        return $this->sourceLine;
    }

    public function setSourceLine($line)
    {
        $this->sourceLine = $line;
    }
}
