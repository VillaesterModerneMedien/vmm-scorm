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

    add_action('plugins_loaded', [$this, 'init'], 0);

	}

    public function activate() {

        $upload_dir = wp_upload_dir();
        $scorm_exports_dir = $upload_dir['basedir'] . "/scorm_exports";
        $directories = [
            $scorm_exports_dir,
            $scorm_exports_dir . "/images",
            $scorm_exports_dir . "/videos",
            $scorm_exports_dir . "/uncanny-snc"
        ];

        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $distribution_dir = VMM_BUILD_PATH . '/dist';
        $dist_css_dir = $distribution_dir . '/css';
        $dist_js_dir = $distribution_dir . '/js';
        $export_css_dir = $scorm_exports_dir. '/css';
        $export_js_dir = $scorm_exports_dir. '/js';

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

		require_once ABSPATH . 'wp-includes/pluggable.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once VMM_PATH . '/helper/helper-functions.php';

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
