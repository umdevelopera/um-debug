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
 *
 * @package um_ext\um_debug
 */
class Profiling {

	const LOCALHOST = '127.0.0.1';

	private $profile_on    = 0;
	private $profile_ip    = array( self::LOCALHOST );
	private $profile_hooks = array( 'umd_profiling' );
	private $profile_hdata = array();

	private $timestart;
	private $timelast;

	private $dump = array();
	private $prof = array();
	private $vars = array();

	public function __construct() {

		// Settings.
		$this->profile_on    = (int) get_option( 'umd_profile_on', $this->profile_on );
		$this->profile_ip    = (array) get_option( 'umd_profile_ip', $this->profile_ip );
		$this->profile_hooks = (array) get_option( 'umd_profile_hooks', $this->profile_hooks );
		$this->profile_hdata = (array) get_option( 'umd_profile_hdata', $this->profile_hdata );

		if ( empty( $this->profile_on ) ) {
			return;
		}

		// Set time.
		$this->timestart = microtime( true );
		$this->timelast  = $this->timestart;

		// Profile hooks.
		foreach ( (array) $this->profile_hooks as $hook ) {
			add_filter( $hook, function( $data ) {
				$hook_name = current_filter();
				$hook_data = in_array( $hook_name, $this->profile_hdata ) ? $data : null;
				$this->save_microtime( $hook_name, $hook_data );
				return $data;
			}, 10 );
		}

		// Show debug_backtrace in the footer.
		if ( in_array( $_SERVER['REMOTE_ADDR'], $this->profile_ip ) ) {
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

	/**
	 * Save the current Unix timestamp with microseconds.
	 * This data will be displayed in the "UM Profiling" section.
	 *
	 * @param string $key  Hook name.
	 * @param mixed  $data Hook data.
	 */
	public function save_microtime( $key = null, $data = null ) {

		$timecurrent     = microtime( true );
		$diff_from_start = number_format( $timecurrent - $this->timestart, 4 );
		$diff_from_prev  = number_format( $timecurrent - $this->timelast, 4 );
		$this->timelast  = $timecurrent;

		$text = "<code>$diff_from_start : $diff_from_prev</code>";
		if ( $key ) {
			$text .= " - $key";
		}
		if ( $data ) {
			$datajson = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			$text    .= " &raquo; <small>$datajson</small>";
		}

		if ( empty( $key ) ) {
			$this->prof[] = $text;
		} elseif ( empty( $this->prof[ $key ] ) ) {
			$this->prof[ $key ] = $text;
		} else {
			$this->prof[ $key . count( $this->prof ) ] = $text;
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
		if ( ! in_array( $_SERVER['REMOTE_ADDR'], $this->profile_ip ) ) {
			return;
		}
		if ( empty( $this->dump ) && empty( $this->prof ) && empty( $this->vars ) ) {
			return;
		}

		echo '<section class="umd-dump">';

		if ( $this->dump ) {
			echo '<p>' . esc_html__( 'UM Backtrace', 'um-debug' ) . '</p>';
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
			echo '<p>' . esc_html__( 'UM Profiling', 'um-debug' ) . '</p>';
			echo '<div class="umd-item">' . esc_html__( 'Time : Delta - Key', 'um-debug' ) . '</div>';
			foreach ( $this->prof as $key => $value ) {
				echo '<div class="umd-item">' . $value . '</div>';
			}
		}

		if ( $this->vars ) {
			echo '<p>' . esc_html__( 'UM Debug Vars', 'um-debug' ) . '</p>';
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

	/**
	 * Render the tab.
	 */
	public function render_page() {
		?>
			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_debug">
				<table class="widefat striped">
					<tbody>
						<tr>
						<th scope="row">
						<label><?php esc_html_e( 'Settings', 'um-debug' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="update_options" class="button button-primary"><?php esc_html_e( 'Save settings', 'um-debug' ); ?></button>
						<span class="um-debug-radio">
							<strong><?php esc_html_e( 'Enable:', 'um-debug' ); ?></strong>
							<label><input type="radio" name="umd_profile_on" value="0" <?php checked( 0, $this->profile_on ) ?>> <?php esc_html_e( 'OFF', 'um-debug' ); ?></label>
							<label><input type="radio" name="umd_profile_on" value="1" <?php checked( 1, $this->profile_on ) ?>> <?php esc_html_e( 'ON', 'um-debug' ); ?></label>
						</span>
						<label>
							<?php esc_html_e( 'Host:', 'um-debug' ); ?>
							<input type="text" name="umd_profile_ip" value="<?php echo implode( ',', $this->profile_ip ); ?>" title="<?php esc_attr_e( 'IP for testing', 'um-debug' ); ?>" class="regular-input" />
						</label>
						</td>
						</tr>
						<tr>
						<th scope="row">
						<label><?php esc_html_e( 'Hooks', 'um-debug' ); ?></label>
						</th>
						<td>
							<textarea name="umd_profile_hooks" class="code medium-text" cols="35" rows="3" placeholder="<?php esc_attr_e( 'Set a timestamp after these hooks', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Set a timestamp after these hooks', 'um-debug' ); ?>"><?php echo implode( ',', $this->profile_hooks ); ?></textarea>
							<textarea name="umd_profile_hdata" class="code medium-text" cols="35" rows="3" placeholder="<?php esc_attr_e( 'Show data in these hooks', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Show data in these hooks', 'um-debug' ); ?>"><?php echo implode( ',', $this->profile_hdata ); ?></textarea>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<div class="postbox">
				<div class="postbox-header">
					<h3 class="hndle"><?php esc_html_e( 'Instruction', 'um-debug' ); ?></h3>
				</div>
				<div class="inside">
					<p><?php esc_html_e( 'List hooks you wish to use for profiling in the "Set a timestamp after these hooks". Separate multiple hooks with a comma.', 'um-debug' ); ?></p>
					<p><?php esc_html_e( 'At the bottom of the page you will see a collapsed panel. Hover over the panel to expand it.', 'um-debug' ); ?></p>
					<p><?php esc_html_e( 'You can use the `umd` function to display a variable in the profiling panel.', 'um-debug' ); ?></p>
					<p><?php esc_html_e( 'You can use the `umdb` function to display a backtrace in the profiling panel.', 'um-debug' ); ?></p>
				</div>
			</div>
		<?php
	}

}
