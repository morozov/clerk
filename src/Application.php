<?php

namespace Clerk;

use Clerk\Parser\SyntaxException;
use Clerk\Parser\Exception;

class Application
{
    public function main($stream = STDIN)
    {
    }

    public function isATty($stream)
    {
        return function_exists('posix_isatty') && posix_isatty($stream);
    }
}
