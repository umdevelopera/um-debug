<?php
/*
 * UM Mail Log page
 */

namespace um_debug;

/**
 * Class Mail_Log
 */
class Mail_Log {

	private $log_mails = true;
	private $log_mails_backtrace = false;
	private $log_mails_hooks = array();
	private $log_mails_subjects = array();
	private $log_mails_rows = 99;

	public function __construct() {

		// Files.
		$upload_dir        = wp_upload_dir();
		$this->logmailpath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'um_mail.log';
		if ( ! file_exists( $this->logmailpath ) ) {
			file_put_contents( $this->logmailpath, '' );
		}

		// Settings.
		$this->log_mails = (int) get_option( 'umd_log_mails', $this->log_mails );
		$this->log_mails_backtrace = (int) get_option( 'umd_log_mails_backtrace', $this->log_mails_backtrace );
		$this->log_mails_hooks = (array) get_option( 'umd_log_mails_hooks', $this->log_mails_hooks );
		$this->log_mails_subjects = (array) get_option( 'umd_log_mails_subjects', $this->log_mails_subjects );
		$this->log_mails_rows = (int) get_option( 'umd_log_mails_rows', $this->log_mails_rows );

		// Log mails after these hooks.
		foreach ( (array) $this->log_mails_hooks as $hook ) {
			add_filter( $hook, function( $data ) {
				$this->log_mails = true;
				return $data;
			}, 10 );
		}

		// Log mails.
		add_filter( 'wp_mail', array( $this, 'log_mail' ), 35 );

		// Menu.
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );

		// Execute handlers.
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Mail Log', 'um-debug' ), __( 'UM Mail Log', 'um-debug' ), 'administrator', 'um_mail_log', array( $this, 'render_mail_log_page' ) );
	}

	public function clear_mail_log() {
		if ( is_file( $this->logmailpath ) ) {
			file_put_contents( $this->logmailpath, '' );

			if ( wp_redirect( admin_url( 'tools.php?page=um_mail_log' ) ) ) {
				exit;
			}
		}
	}

	public function execute_handlers() {
		if ( ! empty( $_REQUEST['action'] ) && 'clear_mail_log' === $_REQUEST['action'] ) {
			$this->clear_mail_log();
		}
	}

	public function get_log_mail_to() {

		$to_arr = array();

		$mail_log_arr = file( $this->logmailpath );

		foreach ( $mail_log_arr as $row ) {
			if ( preg_match( '/^To: (\S+@\S+)/i', $row, $match ) ) {
				array_push( $to_arr, $match[1] );
			}
		}

		$unique_to_arr = array_unique( $to_arr );
		sort( $unique_to_arr );

		return $unique_to_arr;
	}

	public function log_mail( $mail_info ) {

		// Log mails with these subjects
		if ( !$this->log_mails && $this->log_mails_subjects ) {
			foreach ( $this->log_mails_subjects as $subject ) {
				if ( $subject && substr_count( $mail_info['subject'], $subject ) ) {
					$this->log_mails = true;
				}
			}
		}

		if ( $this->log_mails ) {

			$mailto = is_array( $mail_info['to'] ) ? implode( ', ', $mail_info['to'] ) : $mail_info['to'];

			$subject = $mail_info['subject'];

			$message = preg_replace( '/\s+/i', ' ', strip_tags( $mail_info['message'] ) );

			$headers = is_array( $mail_info['headers'] ) ? implode( "\r\n", $mail_info['headers'] ) : trim( $mail_info['headers'] );


			$log = "\r\n"
				. "[" . date( 'Y-m-d H:i:s' ) . "]\r\n"
				. "To: $mailto\r\n"
				. "Subject: $subject\r\n"
				. "Message:\r\n"
				. "$message\r\n";

			// Headers
			if ( !empty( $headers ) ) {
				$log .= "---\r\n"
					. "Headers:\r\n"
					. "$headers\r\n";
			}

			// Request data
			$log .= "---\r\n"
				. "Request:\r\n"
				. "REMOTE_ADDR: {$_SERVER['REMOTE_ADDR']}\r\n"
				. "REQUEST_URI: {$_SERVER['REQUEST_URI']}\r\n";

			// Debug Backtrace
			if ( $this->log_mails_backtrace ) {
				$log .= "---\r\n"
					. "Debug Backtrace:\r\n";
				foreach ( debug_backtrace() as $value ) {
					$text_file = isset( $value['file'] ) ? $value['file'] : '';
					$text_line = isset( $value['line'] ) ? $value['line'] : '';
					$log .= "  $text_file line $text_line\r\n";
				}
			}

			file_put_contents( $this->logmailpath, $log, FILE_APPEND );
			unset( $log );
		}

		return $mail_info;
	}

	public function render_mail_log_page() {
		wp_enqueue_style( 'um-debug' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'UM Mail Log', 'um-debug' ); ?></h1>
			<form method="POST" class="um-debug">
				<input type="hidden" name="page" value="um_mail_log">
				<table class="widefat striped">
					<thead>
						<tr>
						<th scope="row">
						<label><?php _e( 'Actions', 'um-debug' ); ?></label>
						</th>
						<td>
						<button type="submit" name="action" value="clear_mail_log" class="button button-primary"><?php _e( 'Clear log', 'um-debug' ); ?></button>
						<button type="submit" name="action" value="update_options" class="button button-primary"><?php _e( 'Save settings', 'um-debug' ); ?></button>
						<button type="submit" name="umd_log_mails_show" value="list_to" class="button"><?php _e( 'List "to"', 'um-debug' ); ?></button>
						</td>
						</tr>
					</thead>
					<tbody>
						<tr>
						<th scope="row">
						<label><?php _e( 'Settings', 'um-debug' ); ?></label>
						</th>
						<td>
						<label><input type="number" name="umd_log_mails_rows" value="<?php echo esc_attr( $this->log_mails_rows ); ?>" class="small-text" title="<?php esc_attr_e( 'Show rows', 'um-debug' ); ?>" /></label>
						<span class="um-debug-radio">
							<strong><?php _e( 'Enable:', 'um-debug' ); ?></strong>
							<label><input type="radio" name="umd_log_mails" value="0" <?php checked( 0, $this->log_mails ) ?>> <?php _e( 'OFF', 'um-debug' ); ?></label>
							<label><input type="radio" name="umd_log_mails" value="1" <?php checked( 1, $this->log_mails ) ?>> <?php _e( 'ON', 'um-debug' ); ?></label>
						</span>
						<span class="um-debug-radio">
							<strong><?php _e( 'Log backtrace:', 'um-debug' ); ?></strong>
							<label><input type="radio" name="umd_log_mails_backtrace" value="0" <?php checked( 0, $this->log_mails_backtrace ) ?>> <?php _e( 'NO', 'um-debug' ); ?></label>
							<label><input type="radio" name="umd_log_mails_backtrace" value="1" <?php checked( 1, $this->log_mails_backtrace ) ?>> <?php _e( 'YES', 'um-debug' ); ?></label>
						</span>
						</td>
						</tr>
						<tr>
						<th scope="row">
						<label><?php _e( 'Conditions', 'um-debug' ); ?></label>
						</th>
						<td>
							<textarea name="umd_log_mails_hooks" class="code medium-text" cols="35" rows="3" placeholder="<?php esc_attr_e( 'Log mails after these hooks', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Log mails after these hooks', 'um-debug' ); ?>"><?php echo implode( ',', $this->log_mails_hooks ); ?></textarea>
							<textarea name="umd_log_mails_subjects" class="code medium-text" cols="35" rows="3" placeholder="<?php esc_attr_e( 'Log mails with these subjects', 'um-debug' ); ?>" title="<?php esc_attr_e( 'Log mails with these subjects', 'um-debug' ); ?>"><?php echo implode( ',', $this->log_mails_subjects ); ?></textarea>
						</td>
						</tr>
					</tbody>
				</table>
			</form>
			<br />

			<?php
			$show = filter_input( 0, 'umd_log_mails_show' );

			switch ( $show ) {
				case "list_to":
					echo implode( '<br>', $this->get_log_mail_to() );
					break;

				default:
					$log_arr = file( $this->logmailpath );
					$lines = count( $log_arr );
					$start = max( 0, $lines - $this->log_mails_rows );
					for ( $i = $start; $i < $lines; $i++ ) {
						echo htmlspecialchars( $log_arr[$i] ) . '</br>';
					}
					break;
			}
			?>
		</div>
		<?php
	}

}
