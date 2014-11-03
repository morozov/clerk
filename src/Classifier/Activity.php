<?php

namespace Clerk\Classifier;

use Clerk\Timesheet;

class Activity extends Category
{
    private $task;

    private $keywords = array();

    public function __construct($name, Task $task, array $keywords = array())
    {
        parent::__construct($name);
        $this->task = $task;
        $this->keywords = $keywords;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function test(Timesheet $timesheet)
    {
        $subject = $timesheet->getSubject();
        foreach ($this->keywords as $keyword) {
            if (preg_match('/^\/.*\/$/', $keyword)) {
                if (preg_match($keyword . 'i', $subject)) {
                    return true;
                }
            } else {
                if (stripos($subject, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function update(Timesheet $timesheet)
    {
        $timesheet->setActivity($this);
    }
}
