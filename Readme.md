
# Apex Debugger

A lightweight, portable debugger that can easily be used within any application to gather and analyze details of requests.  It supports:

* Combines PSR-3 compliant logging for simplicity, so one call will add line item to both debugger and logger.
* Use any numeric debug level to signify the detail of messages when both, adding debug messages and compiling debug sessions.
* Retrieve and analyze session information via command line tool, rendered HTML, or PHP arrays.
* Saves basic request info, debug messages including caller (file, line, class, method), full backtrace, inputs (post, get, cookie, et al), and any optional items.


## Installation

Install via Composer with:

> `composer require apex/debugger`

## Table of Contents

1. [Debugger](https://github.com/apexpl/debugger/blob/master/docs/debugger.md)
2. Reading Debug Sessions
    1. [Command Line Viewer](https://github.com/apexpl/debugger/blob/master/docs/cli_viewer.md)
    2. [HTML Loader](https://github.com/apexpl/debugger/blob/master/docs/html_loader.md)
    3. [Array Loader](https://github.com/apexpl/debugger/blob/master/docs/array_loader.md)


## Basic Usage

~~~php
use Apex\Debugger\Debugger;

// Init debugger
$debugger = new Debugger(3);   // 3 = detail level of debug messages to log

// Throughout your code, add debug items.
$debugger->add(3, "My debug line item...", 'notice');   // 'notice' will add to PSR-3 logger as well.

// Add more detailed line at level 5, no logging
$debugger->add(5, 'Detailed item');

// At end of request, or within exception handler, finish up and save debug session
$session = $debugger->finish();

// Within exception handler
function handleException(Exception $e) {
    $debugger->finish($e);
}
~~~


## Reading Debug Sessions

Simply run the PHP script located at /vendor/bin/debugger in your installation:

`php ./vendor/bin/debugger`

The menu based command line tool will start showing a list of recently saved debug sessions, which you may enter into.  The next screen shows another menu allowing you to select the exact information you would like to view regarding the request.  Instead of the command line tool, you may also retrieve debug sessions via the [HTML Loader](https://github.com/apexpl/debugger/blob/master/docs/html_loader.md) or the [ArrayLoader](https://github.com/apexpl/debugger/blob/master/docs/array_loader.md).


## Follow Apex

Loads of good things coming in the near future including new quality open source packages, more advanced articles / tutorials that go over down to earth useful topics, et al.  Stay informed by joining the <a href="https://apexpl.io/">mailing list</a> on our web site, or follow along on Twitter at <a href="https://twitter.com/ApexPlatform">@ApexPlatform</a>.


