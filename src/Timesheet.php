<?php

namespace Clerk;

use Clerk\Classifier\Activity;
use Clerk\Timesheet\InvalidArgumentException;

class Timesheet
{
    private $subject;
    private $date;
    private $timeSpent;
    private $activity;

    public function __construct($subject, \DateTime $date, $timeSpent)
    {
        if (!is_string($subject)) {
            throw new InvalidArgumentException(
                sprintf('Subject must be string, %s given', gettype($subject))
            );
        }

        $subject = trim($subject);

        if ($subject === '') {
            throw new InvalidArgumentException('Subject must be not empty');
        }

        if ($timeSpent <= 0) {
            throw new InvalidArgumentException(
                sprintf('Subject must be positive number, %s given', $timeSpent)
            );
        }

        $this->subject = $subject;
        $this->date = $date;
        $this->timeSpent = $timeSpent + 0;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTimeSpent()
    {
        return $this->timeSpent;
    }

    /**
     * @return Activity
     */
    public function getActivity()
    {
        if (!$this->activity) {
            throw new InvalidArgumentException('Activity is not set');
        }

        return $this->activity;
    }

    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;
    }
}
