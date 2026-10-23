<?php
declare(strict_types = 1);

namespace Apex\Debugger\Cli;

use Apex\Debugger\Exceptions\DebuggerSessionNotExistsException;

use Apex\Debugger\Cli\Cli;
use Apex\Debugger\Sessions\Toc;


/**
 * Session CLI commands
 */
class Session
{


    /**
     * Constructor
     */
    public function __construct(
        private string $session_id, 
        private string $index_line
    ) {

        // Check file
        $file = rtrim(sys_get_temp_dir(), '/') . '/debugger/' . $session_id;
        if (!file_exists($file)) { 
            throw new DebuggerSessionNotExistsException("Debugger session does not exist at, $file");
        }
        $this->data = unserialize(file_get_contents($file));

    }

    /**
     * Main screen
     */
    public function mainScreen(bool $show_options = true):void
    {

        // Show options
        if ($show_options === true) {
            $this->showOptions();
        }

        // Set command map
        $command_map = [
            'r' => 'ShowRequest', 
            'n' => 'showNotes', 
            'e' => 'ShowException', 
            't' => 'showTrace', 
            'ip' => ['showInputs', 'post'], 
            'ig' => ['showInputs', 'get'], 
            'ic' => ['showInputs', 'cookie'], 
            'is' => ['showInputs', 'server'], 
            'ih' => ['showInputs', 'http_headers']
        ];

        // Add items to command map
        $x=1;
        foreach ($this->data['items'] as $key => $vars) { 
            $command_map['e' . $x] = ['showItems', $key];
        }

        // Get command
        do { 
            $cmd = Cli::getInput("Command: ");

            // Go back
            $show = false;
            if ($cmd == 'b') { 
                return;
            } elseif (isset($command_map[$cmd]) && is_array($command_map[$cmd])) { 
                call_user_func([$this, $command_map[$cmd][0]], $command_map[$cmd][1]);
                $show = true;
            } elseif (isset($command_map[$cmd])) { 
                $func_name = $command_map[$cmd];
                $this->$func_name();
                $show = true;
            } else { 
                Cli::send("Invalid command.\n");
            }

            // Show options again, if needed
            if ($show === true) {
                $this->showOptions();
            }
        } while (true); 

    }

    /**
     * Show options
     */
    private function showOptions():void
    {

        // Send header
        Cli::sendHeader("Session: $this->session_id");
    $d = $this->data;

        // Go through data
        Cli::send("$this->index_line\n\n");
        Cli::send("    [r] Request (" . count($d['request']) . " items)\n");
        Cli::send("    [n] Notes (" . count($d['notes']) . " items)\n");
        if (count($d['exception']) > 0) { 
            Cli::send("    [e] Exception\n");
        }
        Cli::send("    [t] Trace (" . count($d['backtrace']) . " items)\n");
        Cli::send("    [i] Inputs\n");
        Cli::send("        [ip] POST (" . count($d['inputs']['post']) . " items )\n");
        Cli::send("        [ig] GET (" . count($d['inputs']['get']) . " items )\n");
        Cli::send("        [ic] COOKIE (" . count($d['inputs']['cookie']) . " items )\n");
        Cli::send('        [is] SERVER (' . count($d['inputs']['server']) . " items )\n", 8);
        Cli::send("        [ih] HTTP Headers (" . count($d['inputs']['http_headers']) . " items )\n");

        // Add items
        if (count($d['items']) > 0) { 
            Cli::send("    [ ] Extra Items (" . count($d['items']) . " items)\n");

            $x=1;
            foreach ($d['items'] as $key => $vars) { 
                Cli::send('        [e' . $x . '] $key (' . count($vars) . " items )\n", 8);
            $x++; }
        }

        // Finish menu
        Cli::send("    [b] Back to sessions menu\n");
        Cli::send("    [q] Quit\n\n");
    }

    /**
     * Show request
     */
    private function showRequest():void
    {
        Cli::sendHeader('Request Details');
        print_r($this->data['request']);
    }

    /**
     * Show notes
     */
    private function showNotes(int $start = 0):void
    {

        // Send header
        if ($start == 0) { 
            Cli::sendHeader('Notes');
        }
        Cli::send("Showing " . ($start+1) . ' to ' . ($start + 10) . ' of total ' . count($this->data['notes']) . "\n\n");
        // Go through notes
        for ($x = $start; $x <= ($start + 10); $x++) {
            if (!isset($this->data['notes'][$x])) { 
                break;
            }

            $message = $this->data['notes'][$x]['level'] . ': ' . substr($this->data['notes'][$x]['message'], 0, 50);
            Cli::send("    [" . ($x+1) . "] $message\n");
        }

        // Get command
        do {
            Cli::send("\n[X] View Note #    [m] More    [l] Less    [r] Repeat    [b] Back    [q] Quit\n\n  ");
            $cmd = Cli::getInput("Command: ");

            if (preg_match("/^\d+$/", $cmd) && isset($this->data['notes'][($cmd - 1)])) { 
                $note = $this->data['notes'][($cmd-1)];
                Cli::send("    Note # $cmd\n");
                Cli::send("        Level:  $note[level]\n");
                Cli::send("        File: " . $note['caller']['file'] . ':' . $note['caller']['line'] . "\n");
                Cli::send("        Caller: " . $note['caller']['class'] . '::' . $note['caller']['function'] . "\n");
                Cli::send("        Message:  $note[message]\n\n");

            } elseif (in_array($cmd, ['m','l','r','b'])) { 
                break;
            } else { 
                Cli::send("Invalid Command.");
            }
        } while (true);

        // Execute command
        if ($cmd == 'b') { 
            return;
        } elseif (in_array($cmd, ['m','l','r'])) { 
            if ($cmd == 'm') { $start += 10; }
            if ($cmd == 'l') { $start -= 10; }
            $this->showNotes($start);
        }

    }

    /**
     * Show exception
     */
    private function showException():void
    {
        Cli::sendHeader('Exception');
        print_r($this->data['exception']);
    }

    /**
     * Show trace
     */
    private function showTrace(int $start = 0):void
    {

        // Send header
        if ($start == 0) { 
            Cli::sendHeader('Backtrace');
        }
        Cli::send("Showing " . ($start+1) . ' to ' . ($start + 10) . ' of total ' . count($this->data['backtrace']) . "\n\n");

        // Go through trace
        for ($x = $start; $x <= ($start + 10); $x++) {
            if (!isset($this->data['backtrace'][$x])) { 
                break;
            }
            $t = $this->data['backtrace'][$x];

            // Get args
            $args = [];
            foreach ($t['args'] as $arg) { 
                $args[] = GetType($arg);
            }

            // Get and output message
            $caller = $t['class'] . $t['type'] . $t['function'] . '(' . implode(', ', $args) . ')';
            $message = '(' . $t['file'] . ':' . $t['line'] . ') ' . $caller;
            Cli::send("    [" . ($x+1) . "] $message\n");
        }

        // Get command
        do {
            Cli::send("\n[X] View Item #    [m] More    [l] Less    [r] Repeat    [b] Back    [q] Quit\n\n");
            $cmd = Cli::getInput("Command: ");

            if (preg_match("/^\d+$/", $cmd) && isset($this->data['backtrace'][($cmd - 1)])) { 
                $cmd = (int) ($cmd - 1);
                $this->showTraceItem($cmd);

            } elseif (in_array($cmd, ['m','l','r','b'])) { 
                break;
            } else { 
                Cli::send("Invalid Command.");
            }
        } while (true);

        // Execute command
        if ($cmd == 'b') { 
            return;
        } elseif (in_array($cmd, ['m','l','r'])) { 
            if ($cmd == 'm') { $start += 10; }
            if ($cmd == 'l') { $start -= 10; }
            $this->showTrace($start);
        }

    }

    /**
     * Show trace item
     */
    private function showTraceItem(int $id):void
    {

        // Get trace item
        $item = $this->data['backtrace'][$id];
        Cli::sendHeader("Trace Item $id");

        // Send basic
        Cli::send("    File: " . $item['file'] . ':' . $item['line'] . "\n");
        Cli::send("    Called: " . $item['class'] . $item['type'] . $item['function'] . "()\n");
        Cli::Send("    Arguments:\n\n");

        // Set types
        $types = [
            'boolean' => 'bool', 
            'double' => 'float', 
            'integer' => 'int'
        ];

        // Go through arguments
        $x=1;
        foreach ($item['args'] as $arg) { 

            // Get type
            $type = GetType($arg);
            if (isset($types[$type])) { 
                $type = $types[$type];
            }

            // Get value
            if ($type == 'object') { 
                $value = $arg::class;
            } elseif ($type == 'bool') { 
                $value = $arg === true ? 'true' : 'false';
            } elseif ($type == 'array') { 
                $value = '(' . count($arg) . ' items)';
            } else { 
                $value = $arg;
            }

            // Output arg
            Cli::send("    [$x] ($type) $value\n");
        $x++; }

        // Get command
        do {
            Cli::send("\n[X] View Item #    [r] Repeat    [b] Back    [q] Quit\n\n");
            $cmd = Cli::getInput("Command: ");

            if (preg_match("/^\d+$/", $cmd) && isset($this->data['backtrace'][$id]['args'][($cmd - 1)])) { 
                print_r($this->data['backtrace'][$id]['args'][($cmd - 1)]);

            } elseif (in_array($cmd, ['m','l','r','b'])) { 
                break;
            } else { 
                Cli::send("Invalid Command.");
            }
        } while (true);

        // Execute command
        if ($cmd == 'b') { 
            return;
        } elseif (in_array($cmd, ['m','l','r'])) { 
            $this->showTrace($id);
        }

    }

    /**
     * Show inputs
     */
    private function showInputs(string $var):void
    {

        // Send header
        Cli::sendHeader('Inputs -- ' . strtoupper($var));

        // Go through inputs
        $x=1;
        foreach ($this->data['inputs'][$var] as $key => $value) { 
            if (is_array($value)) { $value = '(array)'; }
            Cli::send("    [$x] $key = $value\n");
        $x++; }

        // Get command
        do {
            Cli::send("\n[X] View Item #    [r] Repeat    [b] Back    [q] Quit\n\n");
            $cmd = Cli::getInput("Command: ");

            if (preg_match("/^\d+$/", $cmd) && isset($this->data['inputs'][$var][($cmd - 1)])) { 
            print_r($this->data['inputs'][$var][($cmd - 1)]);
            } elseif (in_array($cmd, ['m','l','r','b'])) { 
                break;
            } else { 
                Cli::send("Invalid Command.");
            }
        } while (true);

        // Execute command
        if ($cmd == 'b') { 
            return;
        } elseif (in_array($cmd, ['m','l','r'])) { 
            if ($cmd == 'm') { $start += 10; }
            if ($cmd == 'l') { $start -= 10; }
            $this->showInputs($var);
        }
    }

    /**
     * Show items
     */
    private function showItems(string $var, int $start = 0):void
    {

        // Send header
        if ($start == 0) { 
            Cli::sendHeader('Items - ' . ucwords($var));
        }
        Cli::send("Showing " . ($start+1) . ' to ' . ($start + 10) . ' of total ' . count($this->data['items'][$var]) . "\n\n");

        // Go through trace
        for ($x = $start; $x <= ($start + 10); $x++) {
            if (!isset($this->data['items'][$var][$x])) { 
                break;
            }
            Cli::send("    [" . ($x+1) . "] " . (string) $this->data['items'][$var][$x] . "\n");
        }

        // Get command
        do {
            Cli::send("\n[X] View Item #    [m] More    [l] Less    [r] Repeat    [b] Back    [q] Quit\n\n");
            $cmd = Cli::getInput("Command: ");

            if (preg_match("/^\d+$/", $cmd) && isset($this->data['items'][$var][($cmd - 1)])) { 
                print_r($this->data['items'][$var][($cmd - 1)]);
            } elseif (in_array($cmd, ['m','l','r','b'])) { 
                break;
            } else { 
                Cli::send("Invalid Command.");
            }
        } while (true);

        // Execute command
        if ($cmd == 'b') { 
            return;
        } elseif (in_array($cmd, ['m','l','r'])) { 
            if ($cmd == 'm') { $start += 10; }
            if ($cmd == 'l') { $start -= 10; }
            $this->showItems($var, $start);
        }

    }

}



