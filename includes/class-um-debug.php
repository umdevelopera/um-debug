<?php
namespace um_debug;

/**
 * The main class of the "UM Debug tools" plugin.
 */
class UM_Debug {

	private $debug_log;
	private $hook_log;
	private $mail_log;
	private $profiling;
	private $testing_page;

	public function __construct() {

		// Initialize functions.
		include_once 'functions.php';

		// Register assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 10 );

		// Execute handlers
		add_action( 'admin_init', array( $this, 'execute_handlers' ), 20 );

		// Menu.
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 20 );

		// UM Debug Log.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$this->debug_log();
		}

		// UM Hook Log.
		$this->hook_log();

		// UM Mail Log.
		$this->mail_log();

		// Profiling.
		$this->profiling();

		// UM Testing Page.
		$this->testing_page();
	}

	/**
	 * Adds submenu to the "Tools" section.
	 */
	public function add_submenu() {
		add_management_page( __( 'UM Debug', 'um-debug' ), __( 'UM Debug', 'um-debug' ), 'administrator', 'um_debug', array( $this, 'render_page' ) );
	}

	public function enqueue() {
		wp_register_style( 'um-debug', plugins_url( 'um-debug/um-debug.css' ) );
		wp_enqueue_style( 'um-debug' );
	}

	public function execute_handlers() {
		if ( isset( $_REQUEST['action'] ) && 'update_options' === $_REQUEST['action'] ) {
			$this->update_options();
		}
	}

	public function debug_log() {
		if ( empty( $this->debug_log ) ) {
			include_once 'class-debug-log.php';
			$this->debug_log = new Debug_Log;
		}
		return $this->debug_log;
	}

	public function hook_log() {
		if ( empty( $this->hook_log ) ) {
			include_once 'class-hook-log.php';
			$this->hook_log = new Hook_Log;
		}
		return $this->hook_log;
	}

	public function mail_log() {
		if ( empty( $this->mail_log ) ) {
			include_once 'class-mail-log.php';
			$this->mail_log = new Mail_Log;
		}
		return $this->mail_log;
	}

	public function profiling() {
		if ( empty( $this->profiling ) ) {
			include_once 'class-profiling.php';
			$this->profiling = new Profiling;
		}
		return $this->profiling;
	}

	public function testing_page() {
		if ( empty( $this->testing_page ) ) {
			include_once 'class-testing.php';
			$this->testing_page = new Testing;
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

	/**
	 * Render the page.
	 */
	public function render_page() {
		wp_enqueue_style( 'um-debug' );

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		$tab  = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		if ( empty( $tab ) ) {
			$tab = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? 'debug_log' : 'mail_log';
		}

		$tabs_def = array(
			'debug_log'    => __( 'Debug Log' ),
			'mail_log'     => __( 'Mail Log' ),
			'hook_log'     => __( 'Hook Log' ),
			'profiling'    => __( 'Profiling' ),
			'testing_page' => __( 'Test code' ),
		);
		$tabs     = apply_filters( 'um_debug_tabs', $tabs_def );
		?>

		<div class="wrap">
			<h1 class="wp-heading-inline"><?php _e( 'UM Debug tools', 'um-docs' ); ?></h1>
			<h2 class="nav-tab-wrapper um-nav-tab-wrapper">
				<?php foreach ( $tabs as $K => $V ): ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => $page, 'tab' => $K ), admin_url( 'tools.php' ) ) ); ?>" class="nav-tab <?php echo $tab === $K ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $V ); ?></a>
				<?php endforeach; ?>
			</h2>			
			<div id="poststuff">

			<?php
			$method = $tab;
			if ( is_callable( array( $this, $method ) ) ) {
				$this->$method()->render_page();
			}
			?>

			</div>
		</div>

		<?php
	}

}
