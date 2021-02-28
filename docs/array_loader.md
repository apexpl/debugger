
# Array Loader

The `ArrayLoader` class provides an easy method to retrieve information on debug sessions as arrays, which can then be formatted any way you wish such as into JSON objects.  The below table lists the available methods in this class:

Method | Notes
------------- |------------- 
`__construct($session_id = '')` | If session ID (filename within /tmp/debugger/ directory) is specified, the contents of that debug session will be loaded. 
`listSessions()` | Returns array of all saved debug sessions.
`listSessionsByIp(string $ip)` | Same as above method, except will only list sessions created by specified IP address.  If IP is left blank, will use the user's current IP address (ie. yours).
`GetSession()` | Returns full contents of the debug sessions, which is all below methods combined.
`getRequest()` | Returns array of basic request information (host, uri, method, et al).
`getException()` | Returns array of all exception details, if applicable.
`getNotes()` | Returns array of all debug notes / messages.
`getTrace()` | Returns array showing full back trace.
`getInputs(string $type)` | Returns array of all key-value pairs for specified input type.  Supported values for type are:  post, get cookie, server, http_headers
`getItems(string $type)` | Returns list of extra items added to the debug session of the specified type.


**Example**

~~~php
use Apex\Debugger\Sessions\ArrayLoader;

// Load sesssions
$loader = new ArrayLoader();
$session_list = $client->listSessions();

// Load specific session
$session_id = array_keys($session_list)[0];
$loader = new ArrayLoader($session_id);

// Get notes
$notes = $client->getNotes();

// Get all post inputs
$post = $client->getInputs('post');
~~~



