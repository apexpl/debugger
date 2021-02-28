<?php
declare(strict_types = 1);

namespace Apex\Debugger\Sessions;

use Apex\Debugger\Sessions\Toc;
use Apex\Debugger\Exceptions\{DebuggerSessionNotExistsException, DebuggerSessionNotDefinedException, DebuggerInvalidArgumentException};


/**
 * Outputs debugger session into HTML code.
 */
class HtmlLoader
{

    /**
     * Constructor
     */
    public function __construct(
        private string $session_id = '', 
        private bool $include_header = false
    ) {

        // Check for session id
        if ($session_id == '' && isset($_GET['session_id']) && preg_match("/^\d+-\d{5}$/", $_GET['session_id'])) { 
            $this->session_id = $_GET['session_id'];
        }

        // Check file
        if ($this->session_id != '') { 
            $file = rtrim(sys_get_temp_dir(), '/') . '/debugger/' . $this->session_id;
            if (!file_exists($file)) { 
                throw new DebuggerSessionNotExistsException("Debugger session does not exist at, $file");
            }
            $this->data = unserialize(file_get_contents($file));
        } else { 
            $this->data = null;
        }

    }

    /**
     * List sessions
     */
    public function listSessions(string $uri = '', array $toc = null):string
    {

        // Initialize
        $html = $this->getHeader();
        if ($toc === null) { 
            $toc = Toc::get();
        }

        // Get URI
        if ($uri == '') { 
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
        }

        // Start table
        $html = "<h2>Debug Sessions</h2>\n";
        $html .= $this->startHtmlTable(['ID', 'Sessions']);

        // Go through sessions
        foreach ($toc as $index_id => $line) {
            $html .= $this->addHtmlRow([
                "<a href=\"" . $uri . "?session_id=$index_id\">$index_id</a>", 
                $line
            ]);
        }
        $html .= $this->finishHtmlTable();

        // Get footer, and return
        $html .= $this->getFooter();
        return $html;
    }

    /**
     * List sessions by IP address
     */
    public function listSessionsByIp(string $ip = '', string $uri = ''):string
    {
        $toc = Toc::getByIp($ip);
        return $this->listSessions($uri, $toc);
    }

    /**
     * Render
     */
    public function render(bool $only_myip = false):string
    {

        // Get session
        if ($this->data !== null) { 
            return $this->getSession();
        }

        // List sessions
        if ($only_myip === true) { 
            return $this->listSessionsByIp();
        } else { 
            return $this->listSessions();
        }

    }

    /**
     * Get session
     */
    public function getSession():string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Get session
        $html = "<h2>Session ID: $this->session_id</h2><br />";
        $html .= $this->getRequest() . "<br />";
        $html .= $this->getException() . "<br />";
        $html .= $this->getNotes() . "<br />";
        $html .= $this->getTrace() . "<br />";

        // Add inputs
        $html .= "<h3>Inputs</h3><br />";
        foreach (['post','get','cookie','server','http_headers'] as $type) { 
            $html .= $this->getInputs($type) . "<br />";
        }

        // Get Items
        if (count($this->data['items']) > 0) { 
            $html .= "<h3>Items</h3><br />";
            foreach ($this->data['items'] as $key => $vars) { 
                $html .= $this->getItems($key) . "<br />";
            }
        }

        // Return
        return $html;
    }

    /**
     * Get request
     */
    public function getRequest():string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Start table
    $html = "<h3>Request</h3>\n";
        $html .= $this->startHtmlTable(['Key', 'value']);

        // Go through request
        foreach ($this->data['request'] as $key => $value) { 
            $html .= $this->addHtmlRow([$key, $value]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Get exception
     */
    public function getException():string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Start table
    $html = "<h3>Exception</h3>\n";
        $html .= $this->startHtmlTable(['Key', 'value']);

        // Go through request
        foreach ($this->data['exception'] as $key => $value) { 
            $html .= $this->addHtmlRow([$key, $value]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Get notes
     */
    public function getNotes():string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Start table
    $html = "<h3>Notes</h3>\n";
        $html .= $this->startHtmlTable(['Level', 'Note']);

        // Go through notes
        foreach ($this->data['notes'] as $vars) { 
            $file = $vars['caller']['file'] . ':' . $vars['caller']['line'];
            $caller = $vars['caller']['class'] . '::' . $vars['caller']['function'] . '()';
            $note = $vars['message'] . "<br /><br /><b>Caller:</b> $caller ($file)";
            $html .= $this->addHtmlRow([$vars['level'], $note]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Get trace
     */
    public function getTrace():string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Start table
    $html = "<h3>Trace</h3>\n";
        $html .= $this->startHtmlTable(['Caller', 'file', 'Args']);

        // Go through trace
        foreach ($this->data['backtrace'] as $vars) { 
            $file = $vars['file'] . ':' . $vars['line'];
            $caller = $vars['class'] . $vars['type'] . $vars['function'];
            $args = $this->getTraceArgs($vars['args']);

            $html .= $this->addHtmlRow([$caller, $file, $args]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Get trace arguments
     */
    private function getTraceArgs(array $args):string
    {

        // Set types
        $types = [
            'boolean' => 'bool', 
            'double' => 'float', 
            'integer' => 'int'
        ];

        // GO through args
        $html = '<ul>';
        foreach ($args as $arg) { 

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

        // Add to html
        $html .= "<li>($type) $value</li>";
        }
        $html .= "</ul>";

        // Return
        return $html;
    }

    /**
     * Get inputs
     */
    public function getInputs(string $type):string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        } elseif (!in_array($type, ['post','get','cookie','server','http_headers'])) { 
            throw new DebuggerInvalidArgumentException("Invalid input type supplied, $type.  Supported types are:  post, get, cookie, server, http_headers");
        } 

        // Start table
    $html = "<h4>Inputs - " . strtoupper($type) . "</h4>";
        $html .= $this->startHtmlTable(['Key', 'Value']);

        // Go through inputs
        foreach ($this->data['inputs'][$type] as $key => $value) { 
            if (is_array($value)) { $value = "(array)"; }
            $html .= $this->addHtmlRow([$key, $value]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Get items
     */
    public function getItems(string $type):string
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        } elseif (!isset($this->data['items'][$type])) { 
            throw new DebuggerInvalidArgumentException("Invalid item type as it does not exist within the debug session, $type");
        } 

        // Start table
    $html = "<h4>Items - " . strtoupper($type) . "</h4>";
        $html .= $this->startHtmlTable(['Item']);

        // Go through inputs
        foreach ($this->data['items'][$type] as $item) { 
            $html .= $this->addHtmlRow([$item]);
        }
        $html .= $this->finishHtmlTable();

        // Return
        return $html;
    }

    /**
     * Start html table
     */
    private function startHtmlTable(array $columns):string
    {

        $html = '<table class="table table-bordered table-striped table-hover"><thead><tr>' . "\n";
        foreach ($columns as $name) { 
            $html .= "    <th>$name</th>\n";
        }
        $html .= "</tr></thead><tbody>\n";

        // Return
        return $html;

    }

    /**
     * Add htmml tab row
     */
    private function addHtmlRow(array $values):string
    {

        // Get html
        $html = "<tr>\n";
        foreach ($values as $value) { 
            $html .= "    <td>$value</td>\n";
        }
        $html .= "</tr>\n";

        // Return
        return $html;

    }

    /**
     * Finish table
     */
    private function finishHtmlTable():string
    {
        return "</tbody></table>\n\n";
    }

    /**
     * Get header
     */
    private function getHeader():string
    {

        if ($this->include_header === true) { 
            return '<html><head><title>Debugger</title></head><body>' . "\n\n";
        } else { 
            return '';
        }

    }

    /**
     * Get footer
     */
    public function getFooter():string
    {

        if ($this->include_header === true) { 
            return "\n\n</body></html>\n";
        } else { 
            return '';
        }
    }


}

