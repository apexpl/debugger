<?php
declare(strict_types = 1);

namespace Apex\Debugger\Sessions;

use Apex\Debugger\Sessions\Toc;
use Apex\Debugger\Exceptions\{DebuggerSessionNotExistsException, DebuggerSessionNotDefinedException, DebuggerInvalidArgumentException};


/**
 * Retrieve contents of debug session via arrays.
 */
class ArrayLoader
{

    /**
     * Constructor
     */
    public function __construct(
        private string $session_id = '', 
    ) {

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
    public function listSessions():array
    {
        return Toc::get();
    }

    /**
     * List sessions by IP address
     */
    public function listSessionsByIp(string $ip = ''):array
    {
        return Toc::getByIp($ip);
    }

    /**
     * Get session
     */
    public function getSession():array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Return
        return $this->data;
    }

    /**
     * Get request
     */
    public function getRequest():array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Return
        return $this->data['request'];
    }

    /**
     * Get exception
     */
    public function getException():array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Return
        return $this->data['exception'];
    }

    /**
     * Get notes
     */
    public function getNotes():array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Return
        return $this->data['notes'];
    }

    /**
     * Get trace
     */
    public function getTrace():array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        }

        // Return
        return $this->data['backtrace'];
    }

    /**
     * Get inputs
     */
    public function getInputs(string $type):array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        } elseif (!in_array($type, ['post','get','cookie','server','http_headers'])) { 
            throw new DebuggerInvalidArgumentException("Invalid input type supplied, $type.  Supported types are:  post, get, cookie, server, http_headers");
        } 

        // Return
        return $this->data['inputs'][$type];
    }

    /**
     * Get items
     */
    public function getItems(string $type):array
    {

        // Check session exists
        if ($this->data === null) { 
            throw new DebuggerSessionNotDefinedException("No session defined.  You must first pass a 'ession_id' variable to the constructor.");
        } elseif (!isset($this->data['items'][$type])) { 
            throw new DebuggerInvalidArgumentException("Invalid item type as it does not exist within the debug session, $type");
        } 

        // Return
        return $this->data['items'][$type];
    }

}


