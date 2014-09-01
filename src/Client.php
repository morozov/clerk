<?php

namespace Clerk;

use GuzzleHttp\Client as HttpClient;
use IDCT\FileArrayCache as Cache;

class Client
{
    private $httpClient;
    private $cache;
    private $username;
    private $password;

    public function __construct(HttpClient $httpClient, Cache $cache, $username, $password)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->username = $username;
        $this->password = $password;
    }

    public function send(Timesheet $timesheet)
    {
        $this->getAuthData($userId, $oAuthToken);

        $this->httpClient->post(array(
            '/rest/v10/Tasks/{task_id}/link/tasks_ps_timesheets_1',
            array(
                'task_id' => $timesheet->getActivity()->getTask()->getId(),
            ),
        ), array(
            'headers' => array(
                'OAuth-Token' => $oAuthToken,
            ),
            'body' => $this->format($timesheet, $userId),
        ));
    }

    protected function format(Timesheet $timesheet, $userId)
    {
        $activity = $timesheet->getActivity();
        $subject = $timesheet->getSubject();
        $date = $timesheet->getDate();
        $name = sprintf('%s (%s)', $subject, $date->format('m/d/Y'));

        return array(
            'activity_type' => $activity->getName(),
            'tasks_ps_timesheets_1tasks_ida' => $activity->getTask()->getId(),
            'assigned_user_id' => $userId,
            'name' => $name,
            'description' => $subject,
            'activity_date' => $date->format('Y-m-d'),
            'time_spent' => $timesheet->getTimeSpent(),
        );
    }

    private function getAuthData(&$userId, &$oAuthToken)
    {
        if (0 && isset($this->cache['user_id'], $this->cache['token'])) {
            $userId = $this->cache['user_id'];
            $oAuthToken = $this->cache['token'];
        } else {
            $this->authenticate($userId, $oAuthToken);
            $this->cache['user_id'] = $userId;
            $this->cache['token'] = $oAuthToken;
        }
    }

    private function authenticate(&$userId, &$oAuthToken)
    {
        $oAuthToken = $this->getOAuthToken();
        $userId = $this->getUserId($oAuthToken);
    }

    private function getOAuthToken()
    {
        $response = $this->httpClient->post('/rest/v10/oauth2/token', array(
            'body' => array(
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
                'client_id' => 'sugar',
                'client_secret' => '',
            ),
        ));

        $message = $response->json();
        return $message['access_token'];
    }

    private function getUserId($oAuthToken)
    {
        $response = $this->httpClient->get('/rest/v10/me', array(
            'headers' => array(
                'OAuth-Token' => $oAuthToken,
            ),
        ));

        $message = $response->json();
        return $message['current_user']['id'];
    }
}
