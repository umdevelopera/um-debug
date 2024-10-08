# UM Debug tools

Simple tool for logging and testing.

## Key Features:

* Display content of the _debug.log_ file, color errors and warnings.
* Filter content of the _debug.log_ file by the key word.
* Clear _debug.log_ file.
* Log hooks to _um_hook.log_ file, add information about request and backtrace.
* Clear _um_hook.log_ file.
* Log mails to _um_mail.log file_, add information about request and backtrace.
* Log mails after defined hooks. Log mails with defined subjects.
* Clear _um_mail.log_ file.
* Execute custom code.
* Special functions to debug a variable, backtracking and profiling a code.

## Installation

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-debug.git um-debug`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the **UM Debug tools** plugin and click the **Activate** link.

### How to install from ZIP archive

You can install this plugin from the [ZIP file](https://drive.google.com/file/d/1soiwjcvV8ZPzNtWVIG4ftHef1Y-61lNu/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).


## How to use

### How to log PHP errors

Debug logging is disabled by default. Enable debug logging if there is no _debug.log_ file at your site. To enable debug logging you should use FTP to open the _wp-config.php_ file, find the line `define( 'WP_DEBUG', false );` and replace this line with a code snippet below:
```
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```
See also [Debugging in WordPress](https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/).

Go to *wp-admin > Tools > UM Debug Log* to view the _debug.log_ file records.

### How to log hooks

Go to _wp-admin > Tools > UM Hook Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.

List hooks you wish to investigate in the **Hooks** settings. You can list multiple hooks separated by commas.

Save settings.

Do a test. You'll see the hook log records below the settings.

Click the **Clear log** button after testing a hook.

### How to log emails

Go to _wp-admin > Tools > UM Mail Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.

Filter a mail you wish to investigate using the **Conditions** settings. You can filter logging by a specific hook or the email subject. All mails will be logged if **Conditions** are empty.

Save settings.

Send email from the website. You'll see the mail log records below the settings.

Click the **Clear log** button after testing a mail.

### How to execute custom code

Go to _wp-admin > Tools > UM Testing Page_. Enter a code to the textarea and click the "Eval" button.

### Functions for testing and profiling

Add `umd( $var, $key )` to the code for which you want to see a variable value. `$key` (string) - optional label for the variable.

Add `umdb( $key )` to the place in the code for which you want to see a backtrace. `$key` (string) - optional label for the backtrace.

Add `do_action('umd_profiling');` to the place in the code for which you want to see a timestamp.

## Support

Open new [issue](https://github.com/umdevelopera/um-debug/issues) if you are facing a problem or have a suggestion.

## Changelog

### 1.5.0: October 8, 2024

* Optimized plugin structure
* Updated documentation

### 1.4.3: April 5, 2020

* Fixed: Hook log settings

### 1.4.2: March 28, 2020

* Fixed: Frontend styles

### 1.4.1: October 31, 2019

* Added: Hook log

### 1.4.0: October 30, 2019

* Added: defined hooks
* Added: options "Log mails after these hooks" and "Log mails with these subjects"