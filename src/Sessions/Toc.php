<?php
declare(strict_types = 1);

namespace Apex\Debugger\Sessions;



/**
 * Handles the table of contents for saved sessions.
 */
class Toc
{

    /**
     * Get name of toc line
     */
    public static function getIndexLine(int $status, int $tz_offset_mins = 0):string
    {

        // Set variables
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = self::getIpAddress();
        $date = date('M-d H:i:s', time() + ($tz_offset_mins * 60));

        // Set and return line
        $index_line = "(" . $ip . ") $method $uri - $status - $date";
        return $index_line;
    }

    /**
     * Get IP address
     */
    private static function getIpAddress():string
    {

        // Get IP address
        $ip_address = match(true) { 
            isset($_SERVER['HTTP_X_REAL_IP']) => $_SERVER['HTTP_X_REAL_IP'], 
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) => $_SERVER['HTTP_X_FORWARDED_FOR'], 
            isset($_SERVER['REMOTE_ADDR']) => $_SERVER['REMOTE_ADDR'], 
            default => '127.0.0.1'
        };

        // Return
        return $ip_address;

    }

    /**
     * Collect garbage
     */
    public static function collectGarbage(array $toc, int $max_sessions = 20):void
    {

        // Sort toc
        $indexes = array_keys($toc);
        asort($indexes);
        $tmp_dir = rtrim(sys_get_temp_dir(), '/') . '/debugger';

        // Check if we have garbage
        if (count($indexes) < $max_sessions) { 
            file_put_contents("$tmp_dir/toc.json", json_encode($toc, JSON_PRETTY_PRINT));
            return;
        }

        // Delete excess sessions
        do {
            $index_id = array_shift($indexes);
            unset($toc[$index_id]);

            /// Delete JSON file
            if (file_exists("$tmp_dir/$index_id")) { 
                @unlink("$tmp_dir/$index_id");
            }

        } while (count($toc) > $max_sessions);

        // Save toc
        file_put_contents("$tmp_dir/toc.json", json_encode($toc, JSON_PRETTY_PRINT));
    }

    /**
     * Get toc
     */
    public static function get():array
    {

        // Check for .toc file
        $toc_file = rtrim(sys_get_temp_dir(), '/') . '/debugger/toc.json';
        if (!file_exists($toc_file)) { 
            return [];
        }

        // Get toc
        $toc = json_decode(file_get_contents($toc_file), true);
        krsort($toc, SORT_NUMERIC);

        // Return
        return $toc;
    }

    /**
     * Get by IP address
     */
    public static function getByIp(string $ip = ''):array
    {

        // Get TOC and IP address
        $toc = self::get();
        if ($ip == '') { 
            $ip = self::getIpAddress();
        }
        $ip = '(' . $ip . ')';

        // Go through TOC
        $new_toc = [];
        foreach ($toc as $id => $line) { 

            if (!str_starts_with($line, $ip)) { 
                continue;
            }
            $new_toc[$id] = $line;
        }

        // Return
        return $new_toc;
    }

}



