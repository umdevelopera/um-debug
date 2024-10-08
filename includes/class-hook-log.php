<?php
/*
 * UM Hook Log page
 */

namespace um_debug;

/**
 * Class Hook_Log
 */
class Hook_Log {

	private $log_hook = false;
	private $log_hook_backtrace = false;
	private $log_hook_hooks = array();
	private $log_hook_rows = 99;

	public function __construct() {

		// Files.
		$upload_dir        = wp_upload_dir();
		$this->loghookpath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'um_hook.log';
		if ( ! file_exists( $this->loghookpath ) ) {
			file_put_contents( $this->loghookpath, '' );
		}

		// Settings.
		$this->log_hook = (int) get_option( 'umd_log_hook', $this->log_hook );
		$this->log_hook_backtrace = (int) get_option( 'umd_log_hook_backtrace', $this->log_hook_backtrace );
		$this->log_hook_hooks = (array) get_option( 'umd_log_hook_hooks', $this->log_hook_hooks );
		$this->log_hook_rows = (int) get_option( 'umd_log_hook_rows', $this->log_hook_rows );

		// Log these hooks.


		if ( $this->log_hook ) {
			if ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], 'favicon.ico' ) ) {
				return;
			}
			foreach ( (array) $this->log_hook_hooks as $hook ) {
				add_action( $hook, array( $this, 'log_hook' ), 5, 5 );
				add_filter( $hook, array( $this, 'log_hook' ), 5, 5 );
			}
		}

		// Menu.
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );

		// Execute handlers.
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Hook Log', 'um-debug' ), __( 'UM Hook Log', 'um-debug' ), 'administrator', 'um_hook_log', array( $this, 'render_hook_log_page' ) );
	}

	public function clear_hook_log() {
		if ( is_file( $this->loghookpath ) ) {
			file_put_contents( $this->loghookpath, '' );

			if ( wp_redirect( admin_url( 'tools.php?page=um_hook_log' ) ) ) {
				exit;
			}
		}
	}

	public function execute_handlers() {
		if ( ! empty( $_REQUEST['action'] ) && 'clear_hook_log' === $_REQUEST['action'] ) {
			$this->clear_hook_log();
		}
	}

	public function log_hook( $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null ) {

		$log = "\r\n"
			. "[" . date( 'Y-m-d H:i:s' ) . "]\r\n"
			. "Hook: " . current_filter() . "\r\n";

		$args = func_get_args();
		if ( $args ) {
			$argsjson = json_encode( $args );
			$log .= "Args: $argsjson\r\n";
		}

		// Request data
		$log .= "---\r\n"
			. "Request:\r\n"
			. "REMOTE_ADDR: {$_SERVER['REMOTE_ADDR']}\r\n"
			. "REQUEST_URI: {$_SERVER['REQUEST_URI']}\r\n";

		// Debug Backtrace
		if ( $this->log_hook_backtrace ) {
			$log .= "---\r\n"
				. "Debug Backtrace:\r\n";
			foreach ( debug_backtrace() as $value ) {
				$text_file = isset( $value['file'] ) ? $value['file'] : '';
				$text_line = isset( $value['line'] ) ? $value['line'] : '';
				$log .= "  $text_file line $text_line\r\n";
			}
		}

		file_put_contents( $this->loghookpath, $log, FILE_APPEND );
		unset( $log );

		return $arg1;
	}

	public function render_hook_log_page() {
		wp_enqueue_style( 'um-debug' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'UM Hook Log', 'um-debug' ); ?></h1>
			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_hook_log">
				<table class="widefat striped">
					<thead>
						<tr>
						<th scope="row">
						<label><?php _e( 'Actions' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="clear_hook_log" class="button button-primary"><?php _e( 'Clear log' ); ?></button>
						<button type="submit" name="action" value="update_options" class="button button-primary"><?php _e( 'Save settings' ); ?></button>
						</td>
						</tr>
					</thead>
					<tbody>
						<tr>
						<th scope="row">
						<label><?php _e( 'Settings', 'um-debug' ); ?></label>
						</th>
						<td>
						<label><input type="number" name="umd_log_hook_rows" value="<?php echo esc_attr( $this->log_hook_rows ); ?>" class="small-text" title="<?php esc_attr_e( 'Show rows', 'um-debug' ); ?>" /></label>
						<span class="um-debug-radio">
							<strong><?php _e( 'Enable:', 'um-debug' ); ?></strong>
							<label><input type="radio" name="umd_log_hook" value="0" <?php checked( 0, $this->log_hook ) ?>> <?php _e( 'OFF', 'um-debug' ); ?></label>
							<label><input type="radio" name="umd_log_hook" value="1" <?php checked( 1, $this->log_hook ) ?>> <?php _e( 'ON', 'um-debug' ); ?></label>
						</span>
						<span class="um-debug-radio">
							<strong><?php _e( 'Log backtrace:', 'um-debug' ); ?></strong>
							<label><input type="radio" name="umd_log_hook_backtrace" value="0" <?php checked( 0, $this->log_hook_backtrace ) ?>> <?php _e( 'NO', 'um-debug' ); ?></label>
							<label><input type="radio" name="umd_log_hook_backtrace" value="1" <?php checked( 1, $this->log_hook_backtrace ) ?>> <?php _e( 'YES', 'um-debug' ); ?></label>
						</span>
						</td>
						</tr>
						<tr>
						<th scope="row">
						<label><?php _e( 'Hooks' ); ?></label>
						</th>
						<td>
							<textarea name="umd_log_hook_hooks" class="code large-text" cols="35" rows="3" placeholder="<?php esc_attr_e( 'Log these hooks', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Log these hooks', 'um-debug' ); ?>"><?php echo implode( ',', $this->log_hook_hooks ); ?></textarea>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<br />

			<?php
			$log_arr = file( $this->loghookpath );
			$lines = count( $log_arr );
			$start = max( 0, $lines - $this->log_hook_rows );
			for ( $i = $start; $i < $lines; $i++ ) {
				echo htmlspecialchars( $log_arr[$i] ) . '</br>';
			}
			?>
		</div>
		<?php
	}

}
