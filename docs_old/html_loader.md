
# HTML Loader

The `HtmlLoader` class provides an easy to use interface to view debug sessions as rendered HTML, and place into any web page.  


## Render

Simply display the output of the `render()` method within any web page, and that's all you need to view debug sessions:

~~~php
use Apex\Debugger\Sessions\HtmlLoader;

$loader = new HtmlLoader();
echo $loader->render(true);
~~~

This is all you need.  This will initially display a table of all saved debug sessions, the IDs being links which once clicked will take you to the same URI showing the full contents of the debug session in tabular format.

**NOTE:** The `true` boolean passed to the `render()` method above simply signifies to only show debug sessions for requests created from your IP address.  Change this to `false` if you wish to see all debug sessions, regardless of who executed the request.


## Additional Methods

Aside from the `render()` method described above, there are various methods available within this class to retrieve only segments of debug sessions, as described in the below table.

Method | Notes
------------- |------------- 
`__construct($session_id = '')` | If session ID (filename within /tmp/debugger/ directory) is specified, the contents of that debug session will be loaded. 
`listSessions(string $uri = '')` | Returns table of all saved debug sessions.  The optional `$uri` parameter is which URI to link the session IDs to with a `session_id=xxx` added to the query string.  Defaults to the current URI.
`listSessionsByIp(string $ip, string $uri)` | Same as above method, except will only list sessions created by specified IP address.  If IP is left blank, will use the user's current IP address (ie. yours).
`GetSession()` | Returns full contents of the debug sessions, which is all below methods combined.
`getRequest()` | Returns table of basic request information (host, uri, method, et al).
`getException()` | Returns table of all exception details, if applicable.
`getNotes()` | Returns table of all debug notes / messages.
`getTrace()` | Returns table showing full back trace.
`getInputs(string $type)` | Returns table of all key-value pairs for specified input type.  Supported values for type are:  post, get cookie, server, http_headers
`getItems(string $type)` | Returns list of extra items added to the debug session of the specified type.


**Example**

~~~php
use Apex\Debugger\Sessions\HtmlLoader;

$client = new HtmlLoader();
$html_list = $client->listSessions();

// Load specific debug session
$session_id = '7369182435-958114';
$client = new HtmlLoader($session_id);

// Get request table
$req_table = $client->getRequest();

// Get table of all debug notes / messgess
$notes_table = $client->getNotes();

// Get all debug session info, one long page of tables.
$html = $client->getSession();
~~~


