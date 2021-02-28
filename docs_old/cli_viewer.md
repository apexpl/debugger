
# Command Line Viewer

A simple command line utility to view debug sessions is available and located at /vendor/bin/debugger, assuming the package was installed via Composer.  You can start the viewer anytime by running it via command line:

`./vendor/bin/debugger`

This will launch the viewer, and display a list of all saved debug sessions listed by most recent.  Specify the session you would like to view, and the next screen will display a summary menu of all items with corresponding keys on how to view each item (eg. n = notes, t = backtrace, ip = post variables, et al).

Although stated each time when requesting a command, the following keys are accepted globally when viewing a list of items:

* M - More / page down, will display the next 10 items in the list.
* L - Less / page up, will display the previous 10 items in the list.
* R - Repeat, will repeat the same 10 items that were previously displayed.
* B - Back to previous screen / menu.
* Q - Quit, and exit.


### --last Option

When launching the view, use the `--last` option to jump directly into the last saved debug session:

`./vendor/bin/debugger --last`


### --ip &lt;IP_ADDRESS&gt;

Use the `--ip <<IP_ADDRESS>` option to only view debug sessions saved with the specified IP address.

`./vendor/bin/debugger --ip 192.168.0.24`


### --myip

Use the `--myip` option to only show debug sessions that were requested from your IP address, which considering you're running from the command line, will always be 127.0.0.1.

`./vendor/bin/debugger --myip`






