<?php

namespace Apex\Debugger\Test;

use Apex\Debugger\Test\Orders;
use Apex\Debugger\Interfaces\DebuggerInterface;

/**
 * Test users class.
 */
class Users
{

    /**
     * Constructor
     */
    public function __construct(private DebuggerInterface $debugger)
    {
        $this->debugger->add(3, "Init user");
        set_exception_handler([$this, 'handleExceptions']);
    }

    /**
     * Load user
     */
    public function load(int $userid):array
    {

        // Set profile
        $profile = ['id' => $userid, 'username' => 'jsmith', 'email' => 'jsmith@domain.com'];
        $this->debugger->add(2, "Loaded user profile $userid");

        // Get orders
        $profile['orders'] = $this->getOrders($userid, $profile);

        return $this->debugger->finish();
    }

    /**
     * Get orders
     */
    private function getOrders(int $userid, array $profile)
    {

        // Load orders class
        $client = new Orders($this->debugger);
        $client->loadOrders($userid);

    }

    /**
     * Validate user
     */
    public function validate($name)
    {
        $this->checkUsername($name);
    }

    /**
     * Check username
     */
    private function checkUsername($name)
    {
        $this->debugger->add(2, 'checking username');
        throw new \Exception('username is not valid');
    }

    /**
     * Handle exception
     */
    public function handleExceptions(\Exception $e)
    {
echo "YES, HERE"; exit;
        $this->debugger->finish($e);
    }

}


