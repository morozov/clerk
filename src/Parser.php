<?php

namespace Clerk;

use Clerk\Parser\Result;
use Clerk\Parser\SyntaxException;
use Clerk\Timesheet\Exception as TimesheetException;

class Parser
{
    /**
     * @param resource $stream
     * @return Result[]
     */
    public function parse($stream)
    {
        $line = 0;
        $date = null;
        $results = array();

        while (!feof($stream)) {
            try {
                $timesheet = $this->getTimesheet($stream, $line, $date);
                if ($timesheet) {
                    $results[] = $this->getResult($timesheet, null);
                }
            } catch (SyntaxException $e) {
                $results[] = $this->getResult(null, $e);
            }
        }

        return $results;
    }

    private function getTimesheet($stream, &$line, \DateTime &$date = null)
    {
        while ($string = fgets($stream)) {
            $line++;
            $string = trim($string);
            if ($string === '') {
                $date = null;
            } else if ($date === null) {
                $date = $this->parseDate($string, $line);
            } else {
                list($subject, $spent) = $this->parseTimesheet($string, $line);
                $timesheet = $this->createTimesheet($date, $subject, $spent, $line);
                return $timesheet;
            }
        }

        return null;
    }

    /**
     * @param string $string
     * @param string $line
     *
     * @return \DateTime
     * @throws SyntaxException
     */
    private function parseDate($string, $line)
    {
        try {
            return new \DateTime($string);
        } catch (\Exception $e) {
            throw new SyntaxException(
                sprintf('Unable to parse date "%s"', $string),
                $line,
                $e
            );
        }
    }

    private function parseTimesheet($string, $line)
    {
        $parts = explode(' - ', $string, 2);
        if (count($parts) < 2) {
            throw new SyntaxException('Time sheet line has wrong format', $line);
        }

        return $parts;
    }

    /**
     * @param $date
     * @param $subject
     * @param $spent
     * @param $line
     *
     * @return Timesheet
     * @throws SyntaxException
     */
    private function createTimesheet($date, $subject, $spent, $line)
    {
        try {
            return new Timesheet($subject, $date, $spent);
        } catch (TimesheetException $e) {
            throw new SyntaxException('Unable to create timesheet', $line);
        }
    }

    private function getResult($timesheet, $error)
    {
        return new Result($timesheet, $error);
    }
}
