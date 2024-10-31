<?php

/**
 * Plugin Name:       VMM Scorm
 * Plugin URI:        https://villaester.de
 * Description:       Export LearnDash to SCORM
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Kiki Schuelling & Mario Hewera
 * Author URI:        https://villaester.de
 * License:           GPL v2 or later
 * Text Domain:       vmm-helper
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Define plugin constants
define('VMM_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('VMM_URI', untrailingslashit(plugin_dir_url(__FILE__)));
define( 'VMM_PLUGIN_VERSION', '1.0.0' );
define( 'VMM_PLUGIN_MODE', 'prod' ); // prod or dev
define( 'VMM_BUILD_PATH', untrailingslashit( VMM_PATH . '/build' ) );
define( 'VMM_BUILD_URL', untrailingslashit( VMM_URI . '/build' ) );
define( 'VMM_IMAGE_URL', untrailingslashit( VMM_BUILD_URL . '/img' ) );
define( 'VMM_CSS_URL', untrailingslashit( VMM_BUILD_URL . '/css' ) );
define( 'VMM_JS_URL', untrailingslashit( VMM_BUILD_URL . '/js' ) );

require_once 'vendor/autoload.php';

if ( class_exists( 'VmmHelper\VmmScorm' ) ) {

	$VMM_scorm = VMMHelper\VMMScorm::get_instance();
	 register_activation_hook( __FILE__, [$VMM_scorm, 'activate'] );
	 register_deactivation_hook( __FILE__, [$VMM_scorm, 'deactivate'] );
   // Static method for uninstall hook
   register_uninstall_hook( __FILE__, ['VMMHelper\VMMScorm', 'uninstall'] );

}
