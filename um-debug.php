<?php
/**
 * Plugin Name: UM Debug tools
 * Plugin URI:  https://github.com/umdevelopera/um-debug
 * Description: Simple tool for logging and testing: Debug log, Hook log, Mail log, Eval. See the Tools menu.
 * Author:      umdevelopera
 * Author URI:  https://github.com/umdevelopera
 * Text Domain: um-debug
 * Domain Path: /languages
 *
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Version: 1.5.1
 *
 * @package um_ext\um_debug
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class of the UM extension "Debug tools"
 */
class umd {

	private $debug_log;
	private $hook_log;
	private $mail_log;
	private $profiling;
	private $testing_page;

	public function __construct() {

		// Register assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 10 );

		// Execute handlers
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );

		// UM Debug Log.
		$this->debug_log();

		// UM Hook Log.
		$this->hook_log();

		// UM Mail Log.
		$this->mail_log();

		// Profiling.
		$this->profiling();

		// UM Testing Page.
		$this->testing_page();
	}

	public function enqueue() {
		wp_register_style( 'um-debug', plugins_url( 'um-debug.css', __FILE__ ) );
	}

	public function execute_handlers() {
		if ( empty( $_REQUEST['action'] ) ) {
			return;
		}
		if ( 'update_options' === $_REQUEST['action'] ) {
			$this->update_options();
		}
	}

	public function debug_log() {
		if ( empty( $this->debug_log ) ) {
			include_once 'includes/class-debug-log.php';
			$this->debug_log = new um_debug\Debug_Log;
		}
		return $this->debug_log;
	}

	public function hook_log() {
		if ( empty( $this->hook_log ) ) {
			include_once 'includes/class-hook-log.php';
			$this->hook_log = new um_debug\Hook_Log;
		}
		return $this->hook_log;
	}

	public function mail_log() {
		if ( empty( $this->mail_log ) ) {
			include_once 'includes/class-mail-log.php';
			$this->mail_log = new um_debug\Mail_Log;
		}
		return $this->mail_log;
	}

	public function profiling() {
		if ( empty( $this->profiling ) ) {
			include_once 'includes/class-profiling.php';
			$this->profiling = new um_debug\Profiling;
		}
		return $this->profiling;
	}

	public function testing_page() {
		if ( empty( $this->testing_page ) ) {
			include_once 'includes/class-testing.php';
			$this->testing_page = new um_debug\Testing;
		}
		return $this->testing_page;
	}

	public function update_options() {
		if ( empty( $_POST ) ) {
			return;
		}
		$input = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
		foreach ( $input as $key => $value ) {
			if ( ! preg_match( '/^umd_/i', $key ) ) {
				continue;
			}
			if ( is_string( $value ) && substr_count( $value, ',' ) ) {
				$value = array_map( 'trim', explode( ',', $value ) );
			}
			update_option( $key, $value );
		}
		wp_redirect( $_SERVER['REQUEST_URI'] );
	}

}

function umd( $var = null, $key = null, $dublicate = false ) {
	global $umd;

	if ( empty( $umd ) ) {
		$umd = new umd();
	}

	if ( ! is_null( $var ) ) {
		$umd->profiling()->save_var( $var, $key, $dublicate );
	}

	return $umd;
}

function umdb( $key = null ) {
	$backtrace = debug_backtrace( 2 );
	array_shift( $backtrace );

	umd()->profiling()->save_backtrace( $backtrace, $key );

	return $backtrace;
}

umd();
