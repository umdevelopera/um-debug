<?php
/*
 * UM Testing Page
 */

namespace um_debug;

/**
 * Class Testing
 */
class Testing {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );
	}

	public function add_submenu() {
		add_management_page( __( 'UM Testing Page', 'um-debug' ), __( 'UM Testing Page', 'um-debug' ), 'administrator', 'um_testing', array( $this, 'render_testing_page' ) );
	}

	public function render_testing_page() {
		$code = filter_input( 0, 'code' );
		if ( empty( $code ) && isset( $_SESSION['umd_code'] ) ) {
			$code = $_SESSION['umd_code'];
		} else {
			$_SESSION['umd_code'] = $code;
		}
		?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'UM Testing Page', 'um-debug' ); ?></h1>
			<div class="">
				<form method="POST" class="um-debug">
					<textarea name="code" rows="10" style="width: 100%;"><?php echo $code; ?></textarea>
					<p><button type="submit" class="button button-primary"><?php _e( 'Eval', 'um-debug' ); ?></button></p>
				</form>

				<?php
				if ( $code ) {
					echo '<pre>'; eval( $code ); echo '</pre>';
				}
				?>
			</div>
		</div>

		<?php
	}

}
