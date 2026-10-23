<?php
declare(strict_types = 1);

use Apex\Debugger\Debugger;
use Apex\Debugger\Sessions\HtmlLoader;
use Apex\Debugger\Test\Users;
use PHPUnit\Framework\TestCase;


/**
 * Basic debugger tests
 */
class html_loader_test extends TestCase
{

    /**
     * Create and save session
     */
    public function test_backtrace()
    {

        // Delete tmp directory
        $tmp_dir = sys_get_temp_dir() . '/debugger';
        system("rm -rf $tmp_dir");

        // Load files
        require_once(__DIR__ . '/classes/Users.php');
        require_once(__DIR__ . '/classes/Orders.php');

        // Get debugger
        $debugger = new Debugger(3);

        // Load user
        $user = new Users($debugger);
        $session = $user->load(325);

        // Finish debugger
        $this->assertIsArray($session);
        $this->assertIsArray($session['notes']);
        $this->assertCount(3, $session['notes']);

        $this->assertIsArray($session['backtrace']);
        $this->assertCount(12, $session['backtrace']);
        $this->assertEquals('load', $session['backtrace'][0]['function']);
        $this->assertEquals('run', $session['backtrace'][5]['function']);

        $this->assertIsArray($session['exception']);
        $this->assertCount(0, $session['exception']);
        $this->assertEquals(200, $debugger->getStatus());
    }

    /**
     * Test exceptions
     */
    public function test_json()
    {

        // Delete tmp directory
        $tmp_dir = sys_get_temp_dir() . '/debugger';
        $this->assertFileExists("$tmp_dir/toc.json");

        $toc = json_decode(file_get_contents("$tmp_dir/toc.json"), true);
        $this->assertIsArray($toc);
        $this->assertCount(1, $toc);

        $session_id = array_keys($toc)[0];
        $this->assertFileExists("$tmp_dir/$session_id");
        $loader = new HtmlLoader($session_id);

        // Check list
        $html = $loader->listSessions();
        $this->assertTrue(str_contains($html, $session_id));


    }

}


