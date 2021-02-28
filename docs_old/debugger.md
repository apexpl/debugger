
# Debugger

The Apex Debugger has been developed to be as simplistic as possible, while providing you with all necessary information to properly analyize and debug a requst.  Below explains the main `Debugger` class and its methods.

1. <a href="#constructor">Constructor</a>
2. <a href="#add_messages">Add Debug messages</a>
3. <a href="#add_items">Add Extra Debug Items</a>
4. <a href="#finish_session">Finish Session</a>


<a name="constructor"></a>
## Constructor

The constructor accepts the following parameters, all of which are optional:

Variable | Type | Description
------------- |------------- |------------- 
`$debug_level` | int | Maximum level of debug messages to retain and save within sessions.  Defaults to 0, meaning debugging is off.
`$max_sessions` | int | Maximum number of debug sessions to save within /tmp/ directory at one time.  Defaults to 20.
`$rootdir` | string | The root directory of your application, will be stripped away from all filenames saved within debug log for easy readability and security.
 `$logger` | LoggerInterface | Any PSR-3 compliant logger such as Monolog, which if defined, will be called with specified log level when debug items are added.  Defaults to null.
`$tz_offset_mins` | int | Number of minutes to offset times stored within debug sessions.  Defaults to 0.


**Example**

~~~php
use Apex\Debugger\Debugger;
use Monolog\Logger;

$logger = new Logger();

$debugger = new Debugger(
    debug:level: 4, 
    rootdir: '/home/myuser/html', 
    logger: $logger
);
~~~

<a name="add_messages"></a>
## Add Debug Messages

The `$debugger` instance should be added to your dependency injection container and/or passed around your software as necessary.  Adding debug messages is easily done via the `add()` method, which accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$level` | Yes | int | The debug level of the message.  Will only be logged if the debugger was instantiated with a debug level of this level or higher.
`$message` | Yes | string | The debug message.
`$log_level` | No | string | One of the eight supported PSR-3 log levels (info, warning, notice, error, alert, critical, emergency, debug).  Defaults to 'debug', and if a PSR-3 `$logger` was defined during instantiation will add log line.

This will log the message into the debugger, along with the exact filename, line number, class and method that called the `add()` method.  It will be saved when the session finishes, and available in the debug log.

**Example**

~~~php
use Apex\Debugger\Debugger;

$debugger = new Debugger(3);

// Add message
$debugger->add(2, "Message that will be saved...", 'warning');


// Add detailed message, will not be logged as level 4.
$debugger->add(4, 'detailed message');
~~~


<a name="add_items"></a>
## Add Extra Debug Items

Optionally, you may add additional items to a user-defined category to the debug session using the `AddItem()` method, which accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | Yes | string | The type of item, and can be any string you wish (eg. sql, user, et al).
`$data` | Yes | mixed | The data you wish to be added to the debug session.
`$debug_level` | No | int | The debug level of the message, only applicable if you also wish this to be added as a standard debug message.

**Example**

~~~php
use Apex\Debugger\Debugger;

$debugger = new Debugger(3);

// Add extra item
$sql = "SELECT * FROM users WHERE group_id = 2";
$debugger->addItem('sql', $sql);
~~~


<a name="finish_session"></a>
## Finish Session

When the PHP request gracefully ends, and also within your exception handler, you need to call the `finish()` method to finish the debug session.  This will compile all debug session information, and save it into the /tmp/debugger/ directory as a serialized file for later viewing.

This method only takes one optional argument, that being an exception.  For example:

~~~php
use Apex\Debugger\Debugger

// Start debugger
$debugger = new Debugger(3);

// Add debug messages throughout execution
$debugger->add(2, "Some debug message to save");

// Finish session -- $session contains a large array of all debug session info
$session = $debugger->finish();


// Finish within an exception handler
function handleException(Exception $e)
{

    // Finish debug session
    $debugger->finish($e);

    // Handle exception
}
~~~



