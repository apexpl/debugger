<?php

namespace Apex\Debugger\Interfaces;

/**
 * Debugger interface
 * 
 * See: https://github.com/apexpl/debugger
 */
interface DebuggerInterface
{

    /**
     * Add debug message
     */
    public function add(int $level, string $message, string $log_level = 'debug'):void;

    /**
     * Add item
     */
    public function addItem(string $type, mixed $data, int $debug_level = 0):void;

    /**
     * Set status
     */
    public function setStatus(int $status):void;


    /**
     * Get status
     */
    public function getStatus():int;

    /**
     * Set request
     */
    public function setRequest(array $req):void;

    /**
     * Set exception
     */
    public function setException(\Exception $e):void;

    /**
     * Finish session
     */
    public function finish(?\Exception $e = null):array;

}


