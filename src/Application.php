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

    public function main()
    {
        $this->view->writeln('Enter timesheet data and press Ctrl+D:' . PHP_EOL);

        $timesheets = array();
        try {
            while ($this->parser->hasMore()) {
                try {
                    $timesheet = $this->parser->parse();
                    $this->classifier->update($timesheet);
                    $timesheets[] = $timesheet;
                    $this->view->displayTimesheet($timesheet);
                } catch (SyntaxException $e) {
                    $this->view->displayError($e);
                }
            }
        } catch (Exception $e) {
            $this->view->displayError($e);
        }

        if (!$timesheets) {
            $this->view->writeln('Nothing to send. Exiting.');
            return;
        }

        $this->view->prompt('Press Enter to continue or Ctrl+C to abort');

        foreach ($timesheets as $i => $timesheet) {
            $this->client->send($timesheet);
        }
    }
}
