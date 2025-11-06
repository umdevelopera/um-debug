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

	const LOGFILEPATH = '/wp-content/debug.log';

	private $logfilepath;

	public function __construct() {

		// Files.
		$this->logfilepath = ABSPATH . self::LOGFILEPATH;
		if ( ! file_exists( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );
		}

		// Execute handlers.
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );
	}

	/**
	 * The "Clear log" button handler.
	 */
	public function clear_debug_log() {
		if ( is_file( $this->logfilepath ) ) {
			file_put_contents( $this->logfilepath, '' );

			if ( wp_redirect( admin_url( 'tools.php?page=um_debug&tab=debug_log' ) ) ) {
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

		$log_arr = file( $this->logfilepath );
		if ( empty( $log_arr ) ) {
			?>
				<p><?php esc_html_e( 'The log is empty.', 'um-debug' ); ?></p>
			<?php
			return;
		}

		$filter_text = isset( $_POST[ 'umd_log_debug_filter_text' ] ) ? sanitize_text_field( $_POST[ 'umd_log_debug_filter_text' ] ) : get_option( 'umd_log_debug_filter_text' );
		if ( $filter_text ) {
			foreach ( $log_arr as $key => $value ) {
				if ( ! substr_count( $value, $filter_text ) ) {
					unset( $log_arr[ $key ] );
				}
			}
		}

		$debug_rows = (int) get_option( 'umd_log_debug_rows', 999 );
		if ( $debug_rows ) {
			$log_arr = array_slice( $log_arr, -$debug_rows );
		}

		array_walk( $log_arr, array( $this, 'color' ) );
		echo implode( '<br>', $log_arr );
	}

	/**
	 * Render the tab.
	 */
	public function render_page() {
		$debug_rows  = (int) get_option( 'umd_log_debug_rows', 999 );
		$filter_text = isset( $_POST[ 'umd_log_debug_filter_text' ] ) ? sanitize_text_field( $_POST[ 'umd_log_debug_filter_text' ] ) : get_option( 'umd_log_debug_filter_text' );

		?>
			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_debug">
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
							<?php esc_html_e( 'Rows:', 'um-debug' ); ?>
							<input type="number" name="umd_log_debug_rows" value="<?php echo absint( $debug_rows ); ?>" title="<?php esc_attr_e( 'Show rows', 'um-debug' ); ?>" class="um-debug-number" />
						</label>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<div class="postbox">
				<div class="postbox-header">
					<h3 class="hndle"><?php esc_html_e( 'debug.log file', 'um-debug' ); ?></h3>
				</div>
				<div class="inside"><?php $this->render_log(); ?></div>
			</div>
		<?php
	}

}
