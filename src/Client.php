<?php

namespace Clerk;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\ResponseInterface;
use IDCT\FileArrayCache as Cache;

/**
 * SugarInternal Client
 */
class Client
{
    /**
     * HTTP client
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Cache for storing authentication data
     *
     * @var Cache
     */
    private $cache;

    /**
     * SugarInternal username
     *
     * @var string
     */
    private $username;

    /**
     * SugarInternal password
     *
     * @var string
     */
    private $password;

    /**
     * Whether fresh OAuth token has been fetched by current instance
     *
     * @var bool
     */
    private $oAuthTokenFetched = false;

    /**
     * Constructor
     *
     * @param HttpClient $httpClient HTTP client
     * @param Cache      $cache      Authentication data cache
     * @param string     $username   SugarInternal username
     * @param string     $password   SugarInternal password
     */
    public function __construct(HttpClient $httpClient, Cache $cache, $username, $password)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Sends timesheet to SugarInternal
     *
     * @param Timesheet $timesheet Timesheet
     */
    public function send(Timesheet $timesheet)
    {
        $this->sendAuthenticatedRequest('POST', array(
            '/rest/v10/Tasks/{task_id}/link/tasks_ps_timesheets_1',
            array(
                'task_id' => $timesheet->getActivity()->getTask()->getId(),
            ),
        ), array(
            'body' => $this->format($timesheet),
        ));
    }

    /**
     * Formats timesheet for API
     *
     * @param Timesheet $timesheet Timesheet
     *
     * @return array Formatted representation
     */
    protected function format(Timesheet $timesheet)
    {
        $activity = $timesheet->getActivity();
        $subject = $timesheet->getSubject();
        $date = $timesheet->getDate();
        $name = sprintf('%s (%s)', $subject, $date->format('m/d/Y'));

        return array(
            'activity_type' => $activity->getName(),
            'tasks_ps_timesheets_1tasks_ida' => $activity->getTask()->getId(),
            'assigned_user_id' => $this->getUserId(),
            'name' => $name,
            'description' => $subject,
            'activity_date' => $date->format('Y-m-d'),
            'time_spent' => $timesheet->getTimeSpent(),
        );
    }

    /**
     * Sends request with authentication data. In case if server responds 401, new OAuth token is obtained.
     *
     * @param string $method HTTP method
     * @param mixed $url
     * @param array $options
     *
     * @return ResponseInterface
     */
    private function sendAuthenticatedRequest($method, $url, $options = array())
    {
        if (!isset($options['headers'])) {
            $options['headers'] = array();
        }
        $options['headers']['OAuth-Token'] = $this->getOAuthToken();

        $request = $this->httpClient->createRequest($method, $url, $options);

        try {
            return $this->httpClient->send($request);
        } catch (ClientException $e) {
            if ($e->getCode() === 401 && !$this->oAuthTokenFetched) {
                unset($this->cache['token']);
                return call_user_func_array(array($this, __FUNCTION__), func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Returns OAuth token from cache or requests it from API
     *
     * @return string
     */
    private function getOAuthToken()
    {
        if (!isset($this->cache['token'])) {
            if (isset($this->cache['user_id'])) {
                unset($this->cache['user_id']);
            }
            $this->cache['token'] = $this->fetchOAuthToken();
        }

        return $this->cache['token'];
    }

    /**
     * Returns ID of the user corresponding to current OAuth token
     *
     * @return string
     */
    private function getUserId()
    {
        if (!isset($this->cache['user_id'])) {
            $this->cache['user_id'] = $this->fetchUserId();
        }

        return $this->cache['user_id'];
    }

    /**
     * Requests new OAuth token from API
     *
     * @return string
     */
    private function fetchOAuthToken()
    {
        $this->oAuthTokenFetched = true;
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

    /**
     * Requests user ID from API
     *
     * @return string
     */
    private function fetchUserId()
    {
        $response = $this->sendAuthenticatedRequest('GET', '/rest/v10/me');
        $message = $response->json();

        return $message['current_user']['id'];
    }
}
