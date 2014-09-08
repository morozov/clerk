<?php

namespace Clerk;

use Clerk\Parser\Exception;

class View
{
    private $dateFormat;

    public function __construct($dateFormat = 'd M, l')
    {
        $this->dateFormat = $dateFormat;
    }

    public function displayTimesheet(Timesheet $timesheet)
    {
        printf('Date: %s' . PHP_EOL, $timesheet->getDate()->format($this->dateFormat));
        printf('Subject: %s' . PHP_EOL, $timesheet->getSubject());
        printf('Spent: %d' . PHP_EOL, $timesheet->getTimeSpent());
        printf('Activity: %s' . PHP_EOL, $timesheet->getActivity()->getName());
        printf('Task: %s' . PHP_EOL, $timesheet->getActivity()->getTask()->getName());
        printf(PHP_EOL);
    }
}
