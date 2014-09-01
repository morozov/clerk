<?php

namespace Clerk;

use Clerk\Parser\SyntaxException;
use Clerk\Timesheet\Exception as TimesheetException;

class Parser
{
    private $stream;
    private $line;
    private $date;
    private $timesheet;
    private $error;

    public function __construct($stream = STDIN)
    {
        $this->stream = $stream;
    }

    public function hasMore()
    {
        if ($this->timesheet || $this->error) {
            return true;
        }

        $this->next();
        return $this->timesheet || $this->error;
    }

    public function parse()
    {
        try {
            if ($this->error) {
                throw $this->error;
            }
            return $this->timesheet;
        } finally {
            $this->next();
        }
    }

    private function next()
    {
        try {
            $this->timesheet = $this->getTimesheet();
            $this->error = null;
        } catch (SyntaxException $e) {
            $this->timesheet = null;
            $this->error = $e;
        }
    }

    private function getTimesheet()
    {
        while ($string = fgets($this->stream)) {
            $this->line++;
            $string = trim($string);
            if ($string === '') {
                $this->date = null;
            } else if ($this->date === null) {
                $this->date = $this->parseDate($string);
            } else {
                list($subject, $spent) = $this->parseTimesheet($string);
                $timesheet = $this->createTimesheet($subject, $spent);
                return $timesheet;
            }
        }

        return null;
    }

    /**
     * @param string $string
     *
     * @return \DateTime
     * @throws SyntaxException
     */
    private function parseDate($string)
    {
        try {
            return new \DateTime($string);
        } catch (\Exception $e) {
            throw new SyntaxException(
                sprintf('Unable to parse date "%s"', $string),
                $this->line,
                $e
            );
        }
    }

    private function parseTimesheet($string)
    {
        $parts = explode(' - ', $string, 2);
        if (count($parts) < 2) {
            throw new SyntaxException('Time sheet line has wrong format', $this->line);
        }

        return $parts;
    }

    private function createTimesheet($subject, $spent)
    {
        try {
            return new Timesheet($subject, $this->date, $spent);
        } catch (TimesheetException $e) {
            throw new SyntaxException('Unable to create timesheet', $this->line);
        }
    }
}
