<?php

namespace Apex\Debugger\Test;

use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Debugger\Test\Users;

/**
 * Test orders class.
 */
class Orders
{

    /**
     * Constructor
     */
    public function __construct(private DebuggerInterface $debugger)
    {

    }

    /**
     * Load orders
     */
    public function loadOrders(int $userid)
    {

        $orders = ['21312', '1251'];
        $this->debugger->add(3, "Loading orders for $userid");

    }

}


