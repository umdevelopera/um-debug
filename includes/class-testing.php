<?php
/*
 * UM Testing Page
 */

namespace um_debug;

/**
 * Class Testing
 *
 * @package um_ext\um_debug
 */
class Testing {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'code_editor_enqueue_scripts' ) );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Testing', 'um-debug' ), __( 'UM Testing', 'um-debug' ), 'administrator', 'um_testing', array( $this, 'render_page' ) );
	}

	/**
	 * Enqueues assets needed by the code editor.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_code_editor/
	 */
	public function code_editor_enqueue_scripts() {
		if ( 'tools_page_um_testing' === get_current_screen()->id ) {
			// Enqueue code editor and settings for manipulating PHP.
			$settings = wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );

			// Return if the editor was not enqueued.
			if ( false !== $settings ) {
				wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery( function() { wp.codeEditor.initialize( "umd_code", %s ); } );',
						wp_json_encode( $settings )
					)
				);
			}
		}
	}

	public function get_code() {
		$code = filter_input( 0, 'umd_code' );
		if ( empty( $code ) && isset( $_SESSION['umd_code'] ) ) {
			$code = $_SESSION['umd_code'];
		} else {
			$_SESSION['umd_code'] = $code;
		}
		if ( empty( $code ) && get_option( 'umd_code' ) ) {
			$code = get_option( 'umd_code' );
		}
		return $code;
	}

	public function render_eval() {
		if ( ! empty( $_REQUEST['action'] ) && 'umd_eval' === $_REQUEST['action'] ) {
			$code = $this->get_code();
			if ( $code ) {
				echo '<pre>';
				eval( $code );
				echo '</pre>';
			}
		}
	}

	public function render_page() {
		$code = $this->get_code();
		wp_enqueue_style( 'um-debug' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'UM Testing Page', 'um-debug' ); ?></h1>
			<div class="">
				<form method="POST" class="um-debug">
					<input type="hidden" name="page" value="um_testing">
					<table class="widefat striped">
						<thead>
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'Actions', 'um-debug' ); ?></label>
								</th>
								<td>
									<button type="submit" name="action" value="update_options" class="button button-primary"><?php esc_html_e( 'Save code', 'um-debug' ); ?></button>
									<button type="submit" name="action" value="umd_eval" class="button button-primary"><?php esc_html_e( 'Eval', 'um-debug' ); ?></button>
								</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th scope="row">
									<label><?php esc_html_e( 'Snippet', 'um-debug' ); ?></label>
								</th>
								<td>
									<textarea id="umd_code" name="umd_code"><?php echo $code; ?></textarea>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
				<?php $this->render_eval(); ?>
			</div>
		</div>
		<?php
	}

}
