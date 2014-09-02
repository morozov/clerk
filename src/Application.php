<?php

namespace Clerk;

use Clerk\Parser\SyntaxException;
use Clerk\Parser\Exception;

class Application
{
    private $parser;
    private $classifier;
    private $view;
    private $client;

    public function __construct(Parser $parser, Classifier $classifier, View $view, Client $client)
    {
        $this->parser = $parser;
        $this->classifier = $classifier;
        $this->view = $view;
        $this->client = $client;
    }

    public function main($stream = STDIN)
    {
        if ($this->isATty($stream)) {
            $this->view->writeln('Enter timesheet data and press Ctrl+D:' . PHP_EOL);
        }

        $timesheets = array();
        $results = $this->parser->parse($stream);
        if ($this->isATty($stream)) {
            $this->view->writeln('Here\'s what we\'ve got:' . PHP_EOL);
        }

        foreach ($results as $result) {
            if ($result->isError()) {
                $error = $result->getError();
                $this->view->displayError($error);
            } else {
                $timesheet = $result->getTimesheet();
                $this->classifier->update($timesheet);
                $timesheets[] = $timesheet;
                $this->view->displayTimesheet($timesheet);
            }
        }

        if (!$timesheets) {
            $this->view->writeln('Nothing to send. Exiting.');
            return;
        }

        if ($this->isATty($stream)) {
            $this->view->prompt('Press Enter to continue or Ctrl+C to abort');
        }

        foreach ($timesheets as $i => $timesheet) {
            $this->client->send($timesheet);
        }
    }

    public function isATty($stream)
    {
        return function_exists('posix_isatty') && posix_isatty($stream);
    }
}
