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

require_once 'src/VMMScorm.php';

if ( class_exists( 'VMMHelper\VMMScorm' ) ) {

	$VMM_scorm = VMMHelper\VMMScorm::get_instance();
	 register_activation_hook( __FILE__, [$VMM_scorm, 'activate'] );
	 register_deactivation_hook( __FILE__, [$VMM_scorm, 'deactivate'] );
   // Static method for uninstall hook
   register_uninstall_hook( __FILE__, ['VMMHelper\VMMScorm', 'uninstall'] );

}