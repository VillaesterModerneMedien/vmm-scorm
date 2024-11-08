<?php
/**
 * VMMHelper Class.
 *
 * @package VMM-helper
 */

namespace VMMHelper;

/**
 * Class VMMHelper.
 */
class VMMScorm {

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

        // Define plugin constants
        define('VMM_PATH', untrailingslashit(plugin_dir_path(dirname(__FILE__))));
        define('VMM_URI', untrailingslashit(plugin_dir_url(dirname(__FILE__))));
        define( 'VMM_PLUGIN_VERSION', '1.0.0' );
        define( 'VMM_PLUGIN_MODE', 'prod' ); // prod or dev
        define( 'VMM_BUILD_PATH', untrailingslashit( VMM_PATH . '/dist' ) );
        define( 'VMM_BUILD_URL', untrailingslashit( VMM_URI . '/dist' ) );
        define( 'VMM_IMAGE_URL', untrailingslashit( VMM_BUILD_URL . '/img' ) );
        define( 'VMM_CSS_URL', untrailingslashit( VMM_BUILD_URL . '/css' ) );
        define( 'VMM_JS_URL', untrailingslashit( VMM_BUILD_URL . '/js' ) );

        add_action('plugins_loaded', [$this, 'init'], 0);

	}

    public function activate() {

        $upload_dir = wp_upload_dir();
        $scorm_exports_dir = $upload_dir['basedir'] . "/scorm_exports";
        $directories = [
            $scorm_exports_dir,
            $scorm_exports_dir . "/images",
            $scorm_exports_dir . "/videos",
            $scorm_exports_dir . "/pdfs",
            $scorm_exports_dir . "/iframes",
            $scorm_exports_dir . "/uncanny-snc"
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $distribution_dir = VMM_BUILD_PATH;
        $dist_css_dir = $distribution_dir . '/css';
        $dist_js_dir = $distribution_dir . '/js';
        $dist_fonts_dir = $distribution_dir . '/webfonts';
        $export_css_dir = $scorm_exports_dir . '/css';
        $export_js_dir = $scorm_exports_dir . '/js';
        $export_fonts_dir = $scorm_exports_dir . '/webfonts';

        if( !file_exists( $export_fonts_dir ) ) {
          $this->copy_directory($dist_fonts_dir, $export_fonts_dir);
        }

        if( !file_exists( $export_css_dir ) ) {
          $this->copy_directory($dist_css_dir, $export_css_dir);
        }

        if( !file_exists( $export_js_dir ) ) {
          $this->copy_directory($dist_js_dir, $export_js_dir);
        }

    }

	public function deactivate() {

    $upload_dir = wp_upload_dir();
    $scorm_exports_dir = $upload_dir['basedir'] . "/scorm_exports";

    // Check if the directory exists
    if ( file_exists( $scorm_exports_dir ) ) {
        // Function to delete files and directories recursively
        function ecme_delete_directory($dir) {
            if (!is_dir($dir)) {
                return;
            }
            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                  ecme_delete_directory($path); // Recursively delete directories
                } else {
                    unlink($path); // Delete files
                }
            }
            rmdir($dir); // Delete the main directory
        }

        // Call the function to delete the directory
        ecme_delete_directory($scorm_exports_dir);
    }

  }

  /**
   * Static uninstall method.
   */
  public static function uninstall() {
    // Your uninstall code
  }

	/**
	 * Initialize plugin
	 */
	public function init() {

        require_once VMM_PATH . '/src/Assets.php';
        require_once VMM_PATH . '/src/UpdateHandler.php';
        require_once VMM_PATH . '/src/HelperFunctions.php';
        require_once VMM_PATH . '/src/TemplatePages.php';
        require_once VMM_PATH . '/src/UIkitHelper.php';


        require_once ABSPATH . 'wp-includes/pluggable.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';


		 Assets::get_instance();
		 TemplatePages::get_instance();
         UpdateHandler::get_instance();

	} // end class

  public function recursive_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true); // Create the destination directory with correct permissions

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                // Recursively copy subdirectories
                $this->recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                // Copy files
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

public function recursive_delete($dir) {
  if (!file_exists($dir)) {
      return true;
  }

  if (!is_dir($dir) || is_link($dir)) {
      return unlink($dir);
  }

  foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
          continue;
      }

      if (!$this->recursive_delete($dir . "/" . $item)) {
          chmod($dir . "/" . $item, 0777);
          if (!$this->recursive_delete($dir . "/" . $item)) {
              return false;
          }
      }
  }

  return rmdir($dir);
}


 public function copy_directory($source, $destination) {
    // Remove the existing directory if it exists
    if (file_exists($destination)) {
        $this->recursive_delete($destination);
    }

    // Perform the copy operation
    $this->recursive_copy($source, $destination);

}

} // end class
