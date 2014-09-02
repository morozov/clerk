<?php

namespace Clerk\Parser;

use Clerk\Timesheet;

class Result
{
    private $timesheet;
    private $error;

    public function __construct(Timesheet $timesheet = null, SyntaxException $error = null)
    {
        $this->timesheet = $timesheet;
        $this->error = $error;
    }

    public function isError()
    {
        return (bool) $this->error;
    }

    public function getTimesheet()
    {
        return $this->timesheet;
    }

    public function getError()
    {
        return $this->error;
    }
}
