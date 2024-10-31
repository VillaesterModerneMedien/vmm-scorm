<?php
/**
 * @package VMM Helper
 */
namespace VMMHelper;

defined( 'ABSPATH' ) || exit;

class Assets {

  /**
   * Instance of the class.
   *
   * @var TemplatePages
   */
  private static $instance = null;

  /**
   * Get instance of the class.
   *
   * @return TemplatePages
   */
  public static function get_instance() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize.
	 */
	private function init() {
		// Add actions to enqueue and dequeue scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'VMM_enqueue_assets' ), 99 );
	}

	/**
	 * Dequeue and Deregister CSS and JS files.
	 */
	public function VMM_remove_css_js_files() {
		// Dequeue and Deregister CSS files
		wp_dequeue_style( 'elementor-common' );
		// wp_deregister_style('wc-bulk-variations');

		// Dequeue and Deregister JS files
		wp_dequeue_script( 'wc-country-select' );
		wp_dequeue_script( 'elementor-dev-tools' );
		// wp_deregister_script('wc-bulk-variations');
	}

	/**
	 * Get version or file modification time for cache busting.
	 */
	public function get_var( $path ) {
		if ( VMM_PLUGIN_MODE === 'prod' ) {
			return VMM_PLUGIN_VERSION;
		} else {
			return filemtime( VMM_BUILD_PATH . trim( $path ) );
		}
	}


	/**
	 * Localize scripts.
	 */
	private function localize_scripts( $handle ) {
		$data = array(
			'ajax_nonce'        => wp_create_nonce( 'ajax_nonce' ),
			'home_url'          => home_url( '/' ),
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
		);

		wp_localize_script( $handle, 'VMM_scripts_vars', $data );
	}
} // end class
