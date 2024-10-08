<?php
/*
 * Profiling
 *
 * Add `umd( $var, $key )` to the code for which you want to store a variable value.
 *
 * Add `umdb( $key )` to the place in the code for which you want to store a backtrace.
 *
 * Add `do_action('umd_profiling');` to the place in the code for which you want to store a timestamp.
 */

namespace um_debug;

/**
 * Class Profiling
 */
class Profiling {

	private $timestart;
	private $timelast;
	private $dump = array();
	private $prof = array();
	private $vars = array();

	public function __construct() {

		// Settings.
		$this->log_debug_ip = (array) get_option( 'umd_log_debug_ip', '127.0.0.1' );

		// Time.
		$this->timestart = microtime( true );
		$this->timelast  = $this->timestart;

		// Profiling.
		add_action( 'umd_profiling', array( $this, 'save_microtime' ) );

		// Show debug_backtrace in the footer
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_action( 'admin_footer', array( $this, 'show' ), 99 );
			add_action( 'wp_footer', array( $this, 'show' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
		}
	}

	public function enqueue() {
		wp_enqueue_style( 'um-debug' );
	}

	public function save_backtrace( $backtrace, $key = null ) {
		if ( empty( $key ) ) {
			$this->dump[] = $backtrace;
		} elseif ( isset( $this->dump[$key] ) ) {
			$this->dump[$key . count( $this->dump )] = $backtrace;
		} else {
			$this->dump[$key] = $backtrace;
		}
	}

	public function save_microtime( $key = null ) {

		$timecurrent = microtime( true );
		$diff_from_start = number_format( $timecurrent - $this->timestart, 4 );
		$diff_from_prev = number_format( $timecurrent - $this->timelast, 4 );
		$text = "<code>$diff_from_start $diff_from_prev</code> - $key";
		$this->timelast = $timecurrent;

		if ( empty( $key ) ) {
			$this->prof[] = $text;
		} elseif ( isset( $this->prof[$key] ) ) {
			$this->prof[$key . count( $this->prof )] = $text;
		} else {
			$this->prof[$key] = $text;
		}
	}

	public function save_var( $var, $key = null, $dublicate = false ) {
		if ( empty( $key ) ) {
			$this->vars[] = $var;
		} elseif ( isset( $this->vars[$key] ) && $dublicate ) {
			$this->vars[$key . count( $this->vars )] = $var;
		} else {
			$this->vars[$key] = $var;
		}
	}

	public function show() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( ! in_array( $_SERVER['REMOTE_ADDR'], $this->log_debug_ip ) ) {
			return;
		}

		if ( $this->dump || $this->prof || $this->vars ) {
			echo '<section class="umd-dump">';

			if ( $this->dump ) {
				echo '<p>' . __( 'UM Backtrace' ) . '</p>';
				foreach ( $this->dump as $key => $value ) {
					echo '<div class="umd-item">'
						. "<p>Backtrace: $key</p>"
						. '<div>';
					foreach ( $value as $k => $v ) {
						echo isset( $v['file'] ) ? "{$v['file']} : {$v['line']}<br />" : '';
					}
					echo '</div>'
						. '</div>';
				}
			}

			if ( $this->prof ) {
				echo '<p>' . __( 'UM Profiling' ) . '</p>';
				foreach ( $this->prof as $key => $value ) {
					echo '<div class="umd-item">', "<p>$value</p>", '</div>';
				}
			}

			if ( $this->vars ) {
				echo '<p>' . __( 'UM Debug Vars' ) . '</p>';
				foreach ( $this->vars as $key => $value ) {
					echo '<div class="umd-item">'
						. "<p>Variable: $key</p>"
						. '<div>'
						. '<pre>';
					print_r( $value );
					echo '</pre>'
						. '</div>'
						. '</div>';
				}
			}

			echo '</section>';
		}
	}

}