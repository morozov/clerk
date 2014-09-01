<?php

namespace Clerk;

use GuzzleHttp\Client as HttpClient;

class Client
{
    private $userId = '5c85f96c-2226-c8a0-9079-4e9e9acb0c8d';
    private $oAuthToken = '422d3d7e-3b61-7d08-4c5b-54004b14efa9';
    private $httpClient;

    public function __construct(HttpClient $httpClient, $username, $password)
    {
        $this->httpClient = $httpClient;
    }

    public function send(Timesheet $timesheet)
    {
        $this->httpClient->post(array(
            '/rest/v10/Tasks/{task_id}/link/tasks_ps_timesheets_1',
            array(
                'task_id' => $timesheet->getActivity()->getTask()->getId(),
            ),
        ), array(
            'headers' => array(
                'OAuth-Token' => $this->oAuthToken,
            ),
            'body' => $this->format($timesheet),
        ));
    }

    protected function format(Timesheet $timesheet)
    {
        $activity = $timesheet->getActivity();
        $subject = $timesheet->getSubject();
        $date = $timesheet->getDate();
        $name = sprintf('%s (%s)', $subject, $date->format('m/d/Y'));

        return array(
            'activity_type' => $activity->getName(),
            'tasks_ps_timesheets_1tasks_ida' => $activity->getTask()->getId(),
            'assigned_user_id' => $this->userId,
            'name' => $name,
            'description' => $subject,
            'activity_date' => $date->format('Y-m-d'),
            'time_spent' => $timesheet->getTimeSpent(),
        );
    }
}
