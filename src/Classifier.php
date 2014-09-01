<?php
/**
 * Created by PhpStorm.
 * User: morozov
 * Date: 29.08.14
 * Time: 19:16
 */

namespace Clerk;

use Clerk\Classifier\Activity;

class Classifier
{
    /**
     * @var Activity[]
     */
    private $activities;

    /**
     * @var Activity
     */
    private $defaultActivity;

    public function __construct(array $activities, Activity $defaultActivity)
    {
        $this->activities = $activities;
        $this->defaultActivity = $defaultActivity;
    }

    public function update(Timesheet $timesheet)
    {
        foreach ($this->activities as $activity) {
            if ($activity->test($timesheet)) {
                $activity->update($timesheet);
                return;
            }
        }

        $this->defaultActivity->update($timesheet);
    }
}
