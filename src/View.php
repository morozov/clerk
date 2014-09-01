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

    public function displayError(Exception $e)
    {
        printf('Error: %s (line %d)' . PHP_EOL, $e->getMessage(), $e->getSourceLine());
        printf(PHP_EOL);
    }

    public function writeln($contents)
    {
        echo $contents, PHP_EOL;
    }

    public function prompt($message)
    {
        $this->writeln($message);

        $handle = fopen('php://stdin', 'r');
        fgets($handle);
        fclose($handle);
    }
}
