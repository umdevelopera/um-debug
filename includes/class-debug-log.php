<?php
/*
 * UM Hook Log page
 */

namespace um_debug;

/**
 * Class Debug_Log
 *
 * @package um_ext\um_debug
 */
class Debug_Log {

	const LOCALHOST = '127.0.0.1';
	const LOGFILEPATH = '/wp-content/debug.log';

	private $logfilepath;

	public function __construct() {

		// Files.
		$this->logfilepath = ABSPATH . self::LOGFILEPATH;
		if ( ! file_exists( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );
		}

		// Menu.
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );

		// Execute handlers.
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Debug Log', 'um-debug' ), __( 'UM Debug Log', 'um-debug' ), 'administrator', 'um_debug_log', array( $this, 'render_page' ) );
	}

	public function clear_debug_log() {
		if ( is_file( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );

			if ( wp_redirect( admin_url( 'tools.php?page=um_debug_log' ) ) ) {
				exit;
			}
		}
	}

	public function color( &$text ) {
		$text = str_replace(
			array(
				'PHP Fatal error',
				'PHP Error',
				'PHP Warning',
				'PHP Deprecated',
				'PHP Notice',
			),
			array(
				'<span style="color:darkred;">PHP Fatal error</span>',
				'<span style="color:darkred;">PHP Error</span>',
				'<span style="color:darkgoldenrod;">PHP Warning</span>',
				'<span style="color:darkgoldenrod;">PHP Deprecated</span>',
				'<span style="color:darkblue;">PHP Notice</span>',
			),
			$text
		);
		return $text;
	}

	public function execute_handlers() {
		if ( ! empty( $_REQUEST['action'] ) && 'clear_debug_log' === $_REQUEST['action'] ) {
			$this->clear_debug_log();
		}
	}

	public function render_log() {
		if ( ! file_exists( $this->logfilepath ) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'No file "debug.log".', 'um-debug' ); ?></p>
			</div>
			<?php
			return;
		}

		$debug_rows  = (int) get_option( 'umd_log_debug_rows', 999 );
		$filter_text = isset( $_POST[ 'umd_log_debug_filter_text' ] ) ? sanitize_text_field( $_POST[ 'umd_log_debug_filter_text' ] ) : get_option( 'umd_log_debug_filter_text' );

		$debug_log_arr = file( $this->logfilepath );
		if ( $filter_text ) {
			foreach ( $debug_log_arr as $key => $value ) {
				if ( ! substr_count( $value, $filter_text ) ) {
					unset( $debug_log_arr[ $key ] );
				}
			}
		}
		if ( $debug_rows ) {
			$debug_log_arr = array_slice( $debug_log_arr, -$debug_rows );
		}
		array_walk( $debug_log_arr, array( $this, 'color' ) );

		echo implode( '</br>', $debug_log_arr );
	}

	public function render_page() {
		$debug_rows  = (int) get_option( 'umd_log_debug_rows', 999 );
		$debug_ip    = (array) get_option( 'umd_log_debug_ip', self::LOCALHOST );
		$filter_text = isset( $_POST[ 'umd_log_debug_filter_text' ] ) ? sanitize_text_field( $_POST[ 'umd_log_debug_filter_text' ] ) : get_option( 'umd_log_debug_filter_text' );

		wp_enqueue_style( 'um-debug' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'UM Debug Log', 'um-debug' ); ?></h1>
			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_debug_log">
				<table class="widefat striped">
					<thead>
						<tr>
						<th scope="row">
						<label><?php esc_html_e( 'Actions', 'um-debug' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="clear_debug_log" class="button button-primary"><?php esc_html_e( 'Clear log', 'um-debug' ); ?></button>
						<label><input type="text" name="umd_log_debug_filter_text" value="<?php echo $filter_text; ?>" placeholder="<?php esc_attr_e( 'Filter text', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Filter by text', 'um-debug' ); ?>" class="regular-input" /></label>
						<button type="submit" name="action" value="filter_debug_log" class="button"><?php esc_html_e( 'Filter', 'um-debug' ); ?></button>
						</td>
						</tr>
					</thead>
					<tbody>
						<tr>
						<th scope="row">
						<label><?php esc_html_e( 'Settings', 'um-debug' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="update_options" class="button button-primary"><?php esc_html_e( 'Save settings', 'um-debug' ); ?></button>
						<label>
							<?php esc_html_e( 'Host:', 'um-debug' ); ?>
							<input type="text" name="umd_log_debug_ip" value="<?php echo implode( ',', $debug_ip ); ?>" title="<?php esc_attr_e( 'IP for testing', 'um-debug' ); ?>" class="regular-input" />
						</label>
						<label>
							<?php esc_html_e( 'Rows:', 'um-debug' ); ?>
							<input type="number" name="umd_log_debug_rows" value="<?php echo absint( $debug_rows ); ?>" title="<?php esc_attr_e( 'Show rows', 'um-debug' ); ?>" class="um-debug-number" />
						</label>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php $this->render_log(); ?>
		</div>
		<?php
	}

}
