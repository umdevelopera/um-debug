<?php
/**
 * Plugin Name: UM Debug tools
 * Plugin URI:  https://github.com/umdevelopera/um-debug
 * Description: Simple tool for logging and testing: Debug Log, Hook Log, Mail Log, Profiling, Test Code.
 * Author:      umdevelopera
 * Author URI:  https://github.com/umdevelopera
 * Text Domain: um-debug
 * Domain Path: /languages
 *
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Version: 1.6.0
 *
 * @package um_ext\um_debug
 */

defined( 'ABSPATH' ) || exit;

include_once 'includes/class-um-debug.php';
$GLOBALS['umd'] = new um_debug\UM_Debug();
