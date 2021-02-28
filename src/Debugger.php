<?php
declare(strict_types = 1);

namespace Apex\Debugger;

use Apex\Debugger\Sessions\Saver;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Psr\Log\LoggerInterface;

/**
 * Debugger class
 */
class Debugger implements DebuggerInterface
{

    // Properties
    private int $status = 200;
    private array $notes = [];
    private array $items = [];
    private array $request = [];
    private array $exception = [];
    private array $trace = [];

    /**
     * Construct
     */
    public function __construct(
        private int $debug_level = 0, 
        private int $max_sessions = 20, 
        private string $rootdir = '', 
        private ?LoggerInterface $logger = null, 
        private int $tz_offset_mins = 0
    ) {
        $this->start_time = hrtime(true);
        $this->initRequest();
    }

    /**
     * Add debug message
     */
    public function add(int $level, string $message, string $log_level = 'debug'):void
    {

        // Add log, if needed
        if ($this->logger !== null && ($log_level != 'debug' || $this->debug_level >= $level)) { 
            $this->logger->$log_level($message);
        }

        // Check level
        if ($level > $this->debug_level) { 
            return;
        }

        // Add to debug session
        $vars = [
            'level' => $level,  
            'message' => $message, 
            'time' => hrtime(true), 
            'caller' => $this->getCaller(), 
        ];
        array_unshift($this->notes, $vars);
    }

    /**
     * Add item
     */
    public function addItem(string $type, mixed $data, int $debug_level = 0):void
    {

        // Add item
        if (!isset($this->items[$type])) { 
            $this->items[$type] = [];
        }
        array_unshift($this->items[$type], $data);

        // Add debug note, if needed
        if ($debug_level > 0 && is_string($data)) { 
            $message = ucwords($type) . ': ' . $data;
            $this->add($debug_level, $message);
        }
    }

    /**
     * Set status
     */
    public function setStatus(int $status):void
    {
        $this->status = $status;
    }

    /**
     * Get status
     */
    public function getStatus():int 
    {
        return $this->status;
    }

    /**
     * Set request
     */
    public function setRequest(array $req):void
    {
        $this->request = $req;
    }

    /**
     * Init request
     */
    private function initRequest():void
    {

        // Start request
        $this->request = [
            'mode' => php_sapi_name() == 'cli' ? 'cli' : 'http', 
            'host' => $_SERVER['HTTP_HOST'] ?? '', 
            'port' => $_SERVER['SERVER_PORT'] ?? 80, 
            'uri' => $_SERVER['REQUEST_URI'] ?? '', 
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        // Add CLI args to request, if needed
        if ($this->request['mode'] == 'cli') {  
            global $argv;
            $this->request['script_file'] = array_unshift($argv);
            $this->request['argv'] = $argv;
        }
    }

    /**
     * Set exception
     */
    public function setException(\Exception $e):void
    {

        $this->exception = [
            'type' => $e::class, 
            'message' => $e->getMessage(), 
            'file' => ltrim(str_replace($this->rootdir, '', $e->getFile()), '/'), 
            'line' => $e->getLine()
        ];

        // Set properties
        $this->status = 500;
        $this->trace = $e->getTrace();
    }

    /**
     * Get caller
     */
    private function getCaller():array
    {

        // Get caller
        $trace = debug_backtrace();
        $x = isset($trace[2]['class']) && $trace[2]['class'] == __CLASS__ ? 2 : 1;

        // Get caller
        $caller = array(
            'file' => isset($trace[$x]) ? ltrim(str_replace($this->rootdir, '', $trace[$x]['file']), '/') : '', 
            'line' => $trace[$x]['line'] ?? 0,
            'function' => $trace[++$x]['function'] ?? '',
            'class' => $trace[$x]['class'] ?? ''
        );

        // return
        return $caller;
    }

    /**
     * Finish session
     */
    public function finish(?\Exception $e = null):array
    {

        // Check if debugging is off
        if ($this->debug_level == 0) { 
            return [];
        }

        // Set exception, if needed
        if ($e !== null) { 
            $this->setException($e);
        }

        // Start session saver
        $saver = new Saver(
            rootdir: $this->rootdir,  
            max_sessions: $this->max_sessions, 
        tz_offset_mins: $this->tz_offset_mins
        );

        // Save session
        $session = $saver->save(
            $this->status, 
            $this->start_time, 
            $this->notes, 
            $this->items, 
            $this->request, 
            $this->exception, 
            $this->trace
        );

        // Return
        return $session;
    }

}


