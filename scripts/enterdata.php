#!/usr/bin/php
<?php

use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;
use Loek\DB;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

include_once '../init.php';

/**
 * This tool is used to populate the quotes table with quotes.
 * This takes a file/url with all the quotes in separate lines.
 *
 * Run 'php enterdata.php -h' for all options
 *
 * Can also be run by
 *     ./enterdata.php -h
 */
class EnterData extends CLI {

    /**
     * @inheritDoc
     */
    protected function setup(Options $options)
    {
        $options->setHelp("This tool is used to populate the quotes table with quotes from a file or an url.");

        $options->registerCommand('file', 'Gets all rows from a file and enters them in the database');
        $options->registerArgument('file', 'A file with all the quotes on separate lines, context is separated with \' - \' by default', true, 'file');
        $options->registerOption('override', 'Override file with entries that failed to be entered, empty file if all worked', 'o', false, 'file');

        $options->registerCommand('url', 'Gets all rows from an url and enters them in the database');
        $options->registerArgument('url', 'An url to fetch with all the quotes on separate lines, context is separated with \' - \' by default', true, 'url');
        $options->registerOption('delimiter', 'What to split each line on the fetched url with, default is \'\\n\'', 'u', 'delimiter', 'url');

        $options->registerOption('dry-run', 'Dry run', 'd');
        $options->registerOption('separator', 'What to match when separating context on a line, default is \' - \'', 's', 'separator');
    }

    /**
     * @inheritDoc
     */
    protected function main(Options $options)
    {
        if ($options->getOpt('dry-run')) {
            $this->notice('Running in Dry-Run mode, no changes to the database or file will happen');
            echo "\n";
        }
        $lines = new ArrayCollection();
        switch ($options->getCmd()) {
            case 'file':
                try {
                    $file = fopen($options->getArgs()[0], "r");
                    while (!feof($file)) {
                        $lines->add(fgets($file));
                    }
                    fclose($file);
                    $this->success('Loaded all lines');
                } catch (Exception $e) {
                    $this->fatal($e);
                }
                break;
            case 'url':
                $client = new Client();
                try {
                    $this->notice('Fetching from url...');
                    $res = $client->get($options->getArgs()[0]);
                    if ($res->getStatusCode() === 200) {
                        $body = (string) $res->getBody();
                        foreach (explode(stripcslashes($options->getOpt('delimiter', "\n")), $body) as $line) {
                            $lines->add($line);
                        }
                        $this->success('Loaded all lines');
                    } else {
                        $this->error('The url responded with a ' . $res->getStatusCode(), array($res));
                        die(1);
                    }
                } catch (Exception $e) {
                    $this->fatal($e);
                }
                break;
            default:
                echo $options->help();
                exit;
        }

        DB::pdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $ins = DB::pdo()->prepare("insert into quotes (quote, context) values(?, ?)");
        $errors = array();

        $this->notice('Processing lines...');
        $lines = $lines->map(function ($line) {
            return trim($line);
        })->filter(function ($line) use ($options, $ins, &$errors) {
            if (empty($line)) {
                return false;
            }
            try {
                $this->info($line);
                $quote = explode($options->getOpt('separator', ' - '), $line, 2);
                if (!isset($quote[1])) {
                    $quote[1] = '';
                }
                for ($i = 0; $i < count($quote); $i++) {
                    $quote[$i] = trim($quote[$i]);
                }
                if (!$options->getOpt('dry-run')) {
                    return !$ins->execute($quote);
                }
                return false;
            } catch (Exception $e) {
                $errors[$e->getMessage()] = $e;
                return true;
            }
        });
        if ($lines->count() > 0) {
            echo "\n";
            $this->alert('Errors occurred when running the script, errored lines are below:');
            foreach ($lines as $line) {
                $this->error($line);
            }
            $this->alert('The following errors where thrown (use debug loglevel for details):');
            foreach ($errors as $error) {
                $this->debug(get_class($error) . ' caught in ' . $error->getFile() . ':' . $error->getLine());
                $this->debug($error->getTraceAsString());
                $this->critical($error->getMessage());
            }
        } else {
            $this->success('Processed all lines with no errors');
        }
        if ($options->getCmd() === 'file' && $options->getOpt('override')) {
            try {
                $filename = $options->getArgs()[0];
                if (!$options->getOpt('dry-run')) {
                    $file = fopen($filename, "w");
                    foreach ($lines as $line) {
                        fwrite($file, $line . PHP_EOL);
                    }
                    fclose($file);
                    touch($filename);
                }
                $this->success('Updated input file with eventually failed entries');
            } catch (Exception $e) {
                $this->error('Failed to update file with eventually failed entries');
                $this->fatal($e);
            }
        }
    }
}

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

$cli = new EnterData();
$cli->run();
