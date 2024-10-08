<?php
/*
 * UM Hook Log page
 */

namespace um_debug;

/**
 * Class Debug_Log
 */
class Debug_Log {

	const LOCALHOST = '127.0.0.1';
	const LOGFILEPATH = '/wp-content/debug.log';

	private $log_debug_rows = 99;

	public function __construct() {

		// Files.
		$this->logfilepath = ABSPATH . self::LOGFILEPATH;
		if ( ! file_exists( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );
		}

		// Settings.
		$this->log_debug_ip = (array) get_option( 'umd_log_debug_ip', self::LOCALHOST );
		$this->log_debug_rows = (int) get_option( 'umd_log_debug_rows', $this->log_debug_rows );

		// Menu.
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );

		// Execute handlers.
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Debug Log', 'um-debug' ), __( 'UM Debug Log', 'um-debug' ), 'administrator', 'um_debug_log', array( $this, 'render_debug_log_page' ) );
	}

	public function clear_debug_log() {
		if ( is_file( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );

			if ( wp_redirect( admin_url( 'tools.php?page=um_debug_log' ) ) ) {
				exit;
			}
		}
	}

	public function execute_handlers() {
		if ( ! empty( $_REQUEST['action'] ) && 'clear_debug_log' === $_REQUEST['action'] ) {
			$this->clear_debug_log();
		}
	}

	public function render_debug_log_page() {
		$filter_text = filter_input( 0, 'umd_log_debug_filter_text' );
		if ( !$filter_text ) {
			$filter_text = get_option( 'umd_log_debug_filter_text' );
		}

		if ( !file_exists( $this->logfilepath ) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'No file "debug.log".', 'um-debug' ); ?></p>
			</div>
			<?php
			$content = '';
		} else {
			$debug_log_arr = file( $this->logfilepath );
			foreach ( $debug_log_arr as $key => $value ) {
				if ( $filter_text && !substr_count( $value, $filter_text ) ) {
					unset( $debug_log_arr[$key] );
				}
				if ( substr_count( $value, 'PHP Error' ) ) {
					$debug_log_arr[$key] = '<span style="color:darkred;">' . $debug_log_arr[$key] . '</span>';
				}
				if ( substr_count( $value, 'PHP Fatal error' ) ) {
					$debug_log_arr[$key] = '<span style="color:darkred;">' . $debug_log_arr[$key] . '</span>';
				}
				if ( substr_count( $value, 'PHP Notice' ) ) {
					$debug_log_arr[$key] = '<span style="color:darkblue;">' . $debug_log_arr[$key] . '</span>';
				}
				if ( substr_count( $value, 'PHP Warning' ) ) {
					$debug_log_arr[$key] = '<span style="color:darkgoldenrod;">' . $debug_log_arr[$key] . '</span>';
				}
			}

			$content = implode( '</br>', array_slice( array_reverse( $debug_log_arr ), 0, $this->log_debug_rows ) );
		}
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'UM Debug Log', 'um-debug' ); ?></h1>

			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_debug_log">
				<table class="widefat striped">
					<thead>
						<tr>
						<th scope="row">
						<label><?php _e( 'Actions' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="clear_debug_log" class="button button-primary"><?php _e( 'Clear log' ); ?></button>
						<button type="submit" name="action" value="update_options" class="button button-primary"><?php _e( 'Save settings' ); ?></button>
						</td>
						</tr>
					</thead>
					<tbody>
						<tr>
						<th scope="row">
						<label><?php _e( 'Settings' ); ?></label>
						</th>
						<td>
						<label><input type="number"  name="umd_log_debug_rows" value="<?php echo $this->log_debug_rows; ?>" title="<?php _e( 'Show rows' ); ?>" class="small-text" /></label>
						<label><input type="text"  name="umd_log_debug_ip" value="<?php echo implode( ',', $this->log_debug_ip ); ?>" title="<?php _e( 'IP for testing' ); ?>" class="regular-input" /></label>
						<label><input type="text"  name="umd_log_debug_filter_text" value="<?php echo $filter_text; ?>" placeholder="<?php _e( 'Filter text' ); ?>" title="<?php _e( 'Filter by text' ); ?>" class="regular-input" /></label>
						<button type="submit" name="action" value="filter_debug_log" class="button"><?php _e( 'Filter' ); ?></button>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<br />

			<?php echo $content; ?>
		</div>
		<?php
	}

}
