<?php
declare(strict_types = 1);

namespace Apex\Debugger\Cli;

use Apex\Debugger\Cli\{Help, Session};
use Apex\Debugger\Sessions\{Toc, Loader};


/**
 * Handles all CLI functionality within the Debugger.
 */
class Cli
{

    // Properties
    public static array $args = [];
    public static array $options = [];
    public static array $toc = [];

    /**
     * Run CLI command
     */
    public static function run()
    {

        // Get arguments
        list($args, $opt) = self::getArgs();
        $command = array_shift($args) ?? '';

        // Get toc
        if (isset($opt['ip']) && $opt['ip'] != '') {
            self::$toc = Toc::getByIp($opt['ip']);
        } elseif (isset($opt['myip']) && $opt['myip'] == 1) { 
            self::$toc = TOc::getByIp();
        } else { 
            self::$toc = Toc::get();
        }

        // Check for zero sessions
        if (count(self::$toc) == 0) { 
            self::send("No debugging sessions found.  Please create a debugging session, and try again.");
            exit(0);
        }

        // Check for last
        if (isset($opt['last']) && $opt['last'] == 1) { 
            $index_id = array_keys(self::$toc)[0];
            $session = new Session($index_id, self::$toc[$index_id]);
            $session->mainScreen();

        // List toc
        } else { 
            self::listToc();
        }

    }

    /**
     * List toc
     */
    public static function listToc():void
    {

        // Send header
        self::sendHeader('Recent Sessions');

    // Go through toc
        $x=1;  $rev_tox = [];
        foreach (self::$toc as $id => $line) { 
            self::send("[$x] $line\n");
            $rev_toc[(string) $x] = $id;
        $x++; }

        // Get session to enter
        do {
            $id = strtolower(self::getInput("\nSession # to View: "));

            // Check 
            if ($id == 'exit' || $id == 'quit') { 
                self::send("Ok, goodbye.\n");
                exit(0);
            } elseif (isset($rev_toc[$id])) { 
                $index_id = $rev_toc[$id];
                break;
            }
            cli::send("Invalid session choice.  ");
        } while (true);

        // Enter session
        $session = new Session($index_id, self::$toc[$index_id]);
    $session->mainScreen();

        // List TOC again
        self::listToc();
    }

    /**
     * Get command line arguments and options
     */
    public static function getArgs(array $has_value = []):array
    {

        // Initialize
        global $argv;
        list($args, $options, $tmp_args) = [[], [], $argv];
        array_shift($tmp_args);

        // Go through args
        while (count($tmp_args) > 0) { 
            $var = array_shift($tmp_args);

            // Long option with =
            if (preg_match("/^--(\w+?)=(.+)$/", $var, $match)) { 
                $options[$match[1]] = $match[2];

            } elseif (preg_match("/^--(.+)$/", $var, $match) && in_array($match[1], $has_value)) { 


                $value = isset($tmp_args[0]) ? array_shift($tmp_args) : '';
                if ($value == '=') { 
                    $value = isset($tmp_args[0]) ? array_shift($tmp_args) : '';
                }
                $options[$match[1]] = $value;

            } elseif (preg_match("/^--(.+)/", $var, $match)) { 
                $options[$match[1]] = true;

            } elseif (preg_match("/^-(\w+)/", $var, $match)) { 
                $chars = str_split($match[1]);
                foreach ($chars as $char) { 
                    $options[$char] = true;
                }

            } else { 
                $args[] = $var;
            }
        }

        // Set properties
        self::$args = $args;
        self::$options = $options;

        // Return
        return array($args, $options);
    }

    /**
     * Get input from the user.
     */
    public static function getInput(string $label, string $default_value = ''):string
    { 

        // Echo label
        self::send($label);

        // Get input
        $value = strtolower(trim(fgets(STDIN)));
        if ($value == '') { $value = $default_value; }

        // Check quit / exist
        if (in_array($value, ['q', 'quit', 'exit'])) { 
            self::send("Ok, goodbye.\n\n");
            exit(0);
        }

        // Return
        return $value;
    }

    /**
     * Send output to user.
     */
    public static function send(string $data):void
    {
        fputs(STDOUT, $data);
    }

    /**
     * Send header to user
     */
    public static function sendHeader(string $label):void
    {
        self::send("------------------------------\n");
        self::send("-- $label\n");
        self::send("------------------------------\n\n");
    }

}

