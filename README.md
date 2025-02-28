# UM Debug tools

Simple tool for logging and testing.

## Key Features:

* Display WordPress log (the _debug.log_ file) to the screen. Color errors and warnings. Filter log by keyword. Clear log.
* Log detailed information about specific hooks to _um_hook.log_ file and display hooks log to the screen.
* Log mails to _um_mail.log_ file and display mails log to the screen.
* A tool to execute custom PHP code.
* Special features for debugging: display variable value, show backtrace, code profiling.

## Installation

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-debug.git um-debug`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the **UM Debug tools** plugin and click the **Activate** link.

### How to install from ZIP archive

You can install this plugin from the [ZIP file](https://drive.google.com/file/d/1kjufnscL8y12V_pXCZXlBIuECtJ3afXQ/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).

## How to use

### How to log PHP errors

WordPress can save information about PHP issues to the _debug.log_ file. To enable debug logging you should use FTP to open the _wp-config.php_ file, find the line `define( 'WP_DEBUG', false );` and replace this line with a code below:
```
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```
See [Debugging in WordPress](https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/).

Go to *wp-admin > Tools > UM Debug Log* to view the _debug.log_ file records.

### How to log hooks

Go to _wp-admin > Tools > UM Hook Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.
List hooks you wish to investigate in the **Hooks** setting. You can list multiple hooks separated by commas.
Save settings.

Do a test. You'll see the hook log records below the settings.

This tool may be helpful if you need to investigate a redirect. Add the `wp_redirect` hook to the **Hooks** setting then reproduce a redirect. You'll see a redirect backtrace.

**Example 1:** Investigate a redirect.
![UM Hooks Log](https://github.com/user-attachments/assets/25f70d1d-b575-499c-8ff0-5477d4aa6cec)

### How to log emails

Go to _wp-admin > Tools > UM Mail Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.
Filter a mail you wish to investigate using the **Conditions** settings. You can filter logging by a specific hook or the email subject. All mails will be logged if **Conditions** are empty.
Save settings.

Send email from the website. You'll see the mail log records below the settings.

### How to execute custom code

Go to _wp-admin > Tools > UM Testing_. Enter a code to the textarea and click the "Eval" button.

### Functions for testing and profiling

Add `umd( $var, $key )` to the code where you want to see the `$var` variable value. The `$key` parameter is a label for the variable.

Add `umdb( $key )` to the code where you want to see a backtrace. The `$key` parameter is a label for the backtrace.

Add `do_action('umd_profiling');` to the code where you want to see a timestamp. Two timestamps will be shown - the time from the start of code execution and the time from the previous timestamp.

## Support

Open new [issue](https://github.com/umdevelopera/um-debug/issues) if you are facing a problem or have a suggestion.
