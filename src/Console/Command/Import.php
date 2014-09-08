<?php

namespace Clerk\Console\Command;

use Clerk\Classifier;
use Clerk\Client;
use Clerk\Parser;
use Clerk\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Import extends Command
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

        parent::__construct('import');
    }

    protected function configure()
    {
        $this->setDescription('Import timesheets')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If set, the data will be only parsed but not sent'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Enter timesheet data and press Ctrl+D:');

        $timesheets = array();
        $results = $this->parser->parse(STDIN);
        $output->writeln('<info>Here\'s what we\'ve got:</info>');

        foreach ($results as $result) {
            if ($result->isError()) {
                $error = $result->getError();
                $this->getApplication()->renderException($error, $output);
            } else {
                $timesheet = $result->getTimesheet();
                $this->classifier->update($timesheet);
                $timesheets[] = $timesheet;
                $this->view->displayTimesheet($timesheet);
            }
        }

        if (!$timesheets) {
            $output->writeln('Nothing to send. Exiting.');
            return;
        }

        if ($input->getOption('dry-run')) {
            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action? [Y/n]', true);
        $helper->setInputStream(fopen('php://stdin', 'r'));

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $progress = $this->getHelper('progress');

        $progress->start($output, count($timesheets));
        foreach ($timesheets as $timesheet) {
            $this->client->send($timesheet);
            $progress->advance();
        }

        $progress->finish();
    }
} 
