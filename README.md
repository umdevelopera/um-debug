# UM Debug tools

Simple tool for logging and testing.

## Key Features:

* Display WordPress log (the _debug.log_ file) to the screen. Color errors and warnings. Feature: filter log by keyword.
* Log mails to _um_mail.log_ file and display mails log to the screen. Feature: show a list of all email recipients.
* Log information about specific hooks to _um_hook.log_ file and display hooks log to the screen.
* A profiling tool that shows the execution time of specific hooks.
* A testing tool to execute custom PHP code.
* Special features for debugging: display variable value, show backtrace, code profiling.

## Installation

### How to install from GitHub

Open git bash, navigate to the **plugins** folder and execute this command:

`git clone --branch=main git@github.com:umdevelopera/um-debug.git um-debug`

Once the plugin is cloned, enter your site admin dashboard and go to _wp-admin > Plugins > Installed Plugins_. Find the **UM Debug tools** plugin and click the **Activate** link.

### How to install from ZIP archive

You can install this plugin from the [ZIP file](https://drive.google.com/file/d/1sq6nt6vqnB2SMMemP1siSgFbU3A5_4Ga/view) as any other plugin. Follow [this instruction](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin).

## How to use

### How to log PHP errors

WordPress can save information about PHP issues to the _debug.log_ file. 
See [Debugging in WordPress](https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/).
To enable debug logging you should use FTP to open the _wp-config.php_ file, find the line `define( 'WP_DEBUG', false );` and replace this line with a code below:
```
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Go to _wp-admin > Tools > UM Debug > Debug Log_ to view the _debug.log_ file records.

<img width="1900" height="635" alt="1 - Debug Log" src="https://github.com/user-attachments/assets/45a0afdf-adf2-4483-930c-d7e77875be77" />

### How to log emails

Go to _wp-admin > Tools > UM Debug > Mail Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.
Filter a mail you wish to investigate using the **Conditions** settings. You can filter logging by a specific hook or the email subject. All mails will be logged if **Conditions** are empty.
Save settings.

Send email from the website. You'll see the mail log records below the settings.

<img width="1900" height="815" alt="2 - Mail Log" src="https://github.com/user-attachments/assets/c2286dbd-a068-49bb-ab5a-7978c4555a57" />

### How to log hooks

Go to _wp-admin > Tools > UM Debug > Hook Log_.

Turn **ON** the "Enable" setting. Turn **YES** the "Log backtrace" setting if you wish to log detailed info.
List hooks you wish to investigate in the **Hooks** setting. You can list multiple hooks separated by commas.
Save settings.

Do a test. You'll see the hook log records below the settings.

<img width="1900" height="845" alt="3 - Hook Log" src="https://github.com/user-attachments/assets/eb881c7b-ceed-4258-bfa0-958cef9359c8" />

This tool is helpful for investigating strange redirects and user deletions.
- Add the `wp_redirect` hook to the **Hooks** setting to collect redirect details and backtrace.
- Add the `delete_user` hook to the **Hooks** setting to collect details related to user deletion.

### How to profile website

Go to _wp-admin > Tools > UM Debug > Profiling_.

List hooks you wish to investigate in the **Hooks** setting. You can list multiple hooks separated by commas. Save settings.

<img width="1900" height="610" alt="4 - Profiling (admin)" src="https://github.com/user-attachments/assets/7020a8b6-21aa-44cb-8783-0108b8ccb47a" />

Go to the page you wish to investigate. At the bottom you will see a collapsed panel. Hover over the panel to expand it.

Every hook call is shown in a format `Time : Delta - Key`, where
- `Time` is a time from the beginning;
- `Delta` is a time difference from the previous hook;
- `Key` is a hook name.

<img width="1920" height="902" alt="4 - Profiling (front)" src="https://github.com/user-attachments/assets/10738d32-0427-44ec-9e51-d3b95a7c1408" />

#### Functions for testing and profiling

Add `umd( $var, $key )` to the code where you want to see the `$var` variable value. The `$key` parameter is a label for the variable.

Add `umdb( $key )` to the code where you want to see a backtrace. The `$key` parameter is a label for the backtrace.

### How to execute custom code

Go to _wp-admin > Tools > UM Debug > Test code_.

Enter PHP code to the "Snippet" area and click the "Eval" button.

This tool is helpful for calling specific hooks manually.

<img width="1900" height="675" alt="5 - Test Code" src="https://github.com/user-attachments/assets/5e9a96d1-0f55-4491-a3f0-511b52151d67" />

## Support

Open new [issue](https://github.com/umdevelopera/um-debug/issues) if you are facing a problem or have a suggestion.
