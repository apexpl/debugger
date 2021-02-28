<?php
declare(strict_types = 1);

namespace Apex\Debugger\Sessions;

use Apex\Debugger\Debugger;
use Apex\Debugger\Sessions\Toc;

/**
 * Session saver
 */
class Saver
{

    /**
     * Constructor
     */
    public function __construct(
        private string $rootdir = '', 
        private int $max_sessions = 20, 
        private int $tz_offset_mins = 0
    ) { 

    }

    /**
     * Save session
     */
    public function save(
        int $status, 
        int $start_time, 
        array $notes, 
        array $items, 
        array $request, 
        array $exception, 
        array $trace
    ):array {

        // Compile session
        $session = $this->compile($status, $start_time, $notes, $items, $request, $exception, $trace);

        // Get index line
        $index_line = Toc::getIndexLine($status, $this->tz_offset_mins);

        // Create /tmp/ directory, if needed
        $tmp_dir = rtrim(sys_get_temp_dir(), '/') . '/debugger';
        if (!is_dir($tmp_dir)) { 
            mkdir($tmp_dir);
        }

        // Save file
        $filename = time() . '-' . rand(10000, 99999);
        file_put_contents("$tmp_dir/$filename", serialize($session));

        // Add to table of contents
        $toc = file_exists("$tmp_dir/toc.json") ? json_decode(file_get_contents("$tmp_dir/toc.json"), true) : [];
        $toc[$filename] = $index_line;

        // Collect garbage
        Toc::collectGarbage($toc, $this->max_sessions);

        // Return
    return $session;
    }

    /**
     * Compile session
     */
    private function compile(
        int $status, 
        int $start_time, 
        array $notes, 
        array $items, 
        array $request, 
        array $exception, 
        array $trace
    ):array {

        // Set session
        $session = [
            'status' => $status, 
            'start_time' => $start_time, 
            'end_time' => hrtime(true), 
            'notes' => $notes, 
            'items' => $items, 
            'request' => $request, 
            'exception' => $exception, 
            'backtrace' => $this->getBacktrace($trace), 
            'inputs' => [
                'post' => filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING) ?? [], 
                'get' => filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING) ?? [],  
                'cookie' => filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING) ?? [],  
                'server' => filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING) ?? [],  
                'http_headers' => function_exists('getAllHeaders') ? getAllHeaders() : []
            ]
        ];

        // Return
        return $session;

    }

    /**
     * Get backtrace
     */
    private function getBacktrace(array $trace = []):array
    {

        // Get backtrace
        if (count($trace) == 0) { 
            $trace = debug_backtrace();
            array_splice($trace, 0, 4);
        }
        $stack = [];

        // Create trace
        foreach ($trace as $vars) { 
            if (!isset($vars['file'])) { continue; }

            $stack[] = [
                'file' => ltrim(str_replace($this->rootdir, '', $vars['file']), '/'), 
                'line' => $vars['line'] ?? 0,
                'type' => $vars['type'] ?? '', 
                'args' => $vars['args'] ?? [], 
                'function' => $vars['function'] ?? '',
                'class' => $vars['class'] ?? '' 
            ];

        }

        // Return
        return $stack;
    }

}


