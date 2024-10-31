<?php
/**
 * This class is about
 *
 * @package Ecom Helper
 * @version 1.0.0
 * @license GPL-2.0+
 */

namespace EcomHelper;

defined( 'ABSPATH' ) || exit;

class UpdateHandler {

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

		$this->hooks();
	}

	public function hooks() {

    add_filter('post_row_actions', [$this, 'add_custom_button_to_post_row_actions'], 10, 2);
    
    add_action('wp_ajax_export_scorm', [$this, 'export_scorm']);

    add_action('admin_footer', [$this, 'add_custom_script_to_admin_footer']);

  }

  public function export_scorm(){
 
    // Ensure this script is only accessible via AJAX
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        wp_die('Access denied');
    }
    
    // Security check
    check_ajax_referer('ajax_nonce', 'security', false) || wp_die('Security check failed');
    
    // Required fields
    $required_fields = ['post_type', 'post_id'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(__('Something went wrong. Please try again later.', 'ecom-scorm'));
            exit;
        }
    }
    
    // Sanitize inputs
    $lesson_type = sanitize_text_field($_POST['post_type']);
    $lesson_id = absint($_POST['post_id']);
    $lesson = get_post($lesson_id);
    $lesson_title = $lesson->post_title;
    $lesson_slug = $lesson->post_name;
    $lesson_content = $lesson->post_content;
    $course_id = absint($_POST['course_id'] ?? 0);

    // Clear the export directory before creating new files
    $upload_dir = wp_upload_dir();
    $export_directory = "{$upload_dir['basedir']}/scorm_exports";

    // Clear the scorm_exports directory excluding css, js, images,videos and uncanny-snc
    ecom_clear_directory($export_directory, ['css', 'js', 'images', 'videos', 'uncanny-snc']);

    // Clear the contents of uncanny-snc directory but keep the directory itself
    $uncanny_snc_directory = "$export_directory/uncanny-snc";
    $images_directory = "$export_directory/images";
    $videos_directory = "$export_directory/videos";
    ecom_clear_directory($uncanny_snc_directory);
    ecom_clear_directory($images_directory);
    ecom_clear_directory($videos_directory);

    $html__content = ecom_get_scorm_html($lesson_id, $lesson_content);

    if( $html__content ) $html_content = ecom_convert_vc_sync_to_iframe($lesson_id, $html__content);

    $images_videos_files = [$lesson_id => $html_content[0]];

    $this->create_html_file($html_content[1], 'lessons/' . $lesson_slug . '.html', $export_directory);
 
    // Generate SCORM manifest
    $scorm_manifest = $this->generate_single_lession_scorm_manifest($lesson_id, $lesson_slug, $lesson_title, $images_videos_files);
    
    // Save the SCORM manifest
    $manifest_path = $export_directory . '/imsmanifest.xml';
  
    file_put_contents($manifest_path, $scorm_manifest);

    $destination = "$export_directory/lesson_$lesson_id.zip";
    // Create the new SCORM package
    $this->create_scorm_package($export_directory, $destination);
    // Delete the imsmanifest.xml file and lessons folder
    unlink($manifest_path);
    $this->delete_directory($export_directory . '/lessons');

    $zip_url = "{$upload_dir['baseurl']}/scorm_exports/lesson_$lesson_id.zip";
    // Return the URL of the ZIP file in the AJAX response
    wp_send_json_success(['url' => $zip_url]);
    exit;

} // end function

// Function to add custom script to the admin footer
public function add_custom_script_to_admin_footer() { ?>

 <script type="text/javascript">

 function ecom_export_scorm(el) {
  
  let _this = jQuery(el);
  let post_id = _this.attr("post_id");
  let course_id = _this.attr("course_id");
  let post_type = _this.attr("post_type");
  let _text = _this.text('Exporting....');

  jQuery.ajax({
  type: "POST",
  url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
  data: {
      'action' : 'export_scorm',
      'security' : "<?php echo wp_create_nonce('ajax_nonce'); ?>",
      'post_type' : post_type,
      'post_id' : post_id,
      'course_id' : course_id,
  },
  
  dataType: "json",
  
  success: function(response) {
            if (response.success) {
              var link = document.createElement('a');
                    link.href = response.data.url;
                    link.download = `lesson_${post_id}.zip`;
                    // Append the link to the body
                    document.body.appendChild(link);

                    // Trigger click event
                    if (document.createEvent) {
                        var event = document.createEvent('MouseEvents');
                        event.initEvent('click', true, true);
                        link.dispatchEvent(event);
                    } else {
                        link.click();
                    }

                    // Remove the link from the body
                    document.body.removeChild(link);
                    _this.text('Export as SCORM');

                } else {
                    _this.text('Export as SCORM');
                    alert('Failed to create SCORM Package. Please try again.');
                  
                }
            },
            error: function(response) {
              _this.text('Export as SCORM');
              // console.log(response.responseText);
              alert('An error occurred. Please try again.');
            }
  
   });

 } // end function
 
 </script> <?php
}

// Add custom button to post row actions
public function add_custom_button_to_post_row_actions($actions, $post) {
  // Check if the post type is 'post' (you can change it to any custom post type if needed)
  if ($post->post_type == 'sfwd-lessons') {

      // Get the course ID using LearnDash function
      $course_id = learndash_get_course_id($post->ID);
      $export_scorm_button = '<a href="javascript:void(0)" post_id="'.$post->ID.'" course_id="'. $course_id .'" post_type="'.$post->post_type.'" onclick="ecom_export_scorm(this)">Export as SCORM</a>';
      $actions['export_scorm_button'] = $export_scorm_button;
  }
  return $actions;
}

private function generate_single_lession_scorm_manifest($lesson_id, $lesson_slug, $lesson_title, $images_videos_files) {

    ob_start();
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?><manifest xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2"
        xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        identifier="com.example.scorm.demo" version="1.2"
        xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd
                            http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd">

    <metadata>
        <schema>ADL SCORM</schema>
        <schemaversion>1.2</schemaversion>
        <!-- API and alternate links -->
        <link rel="https://api.w.org/" href="<?php echo esc_url(home_url('wp-json')); ?>"/>
        <link rel="alternate" type="application/json" href='<?php echo home_url("wp-json/wp/v2/pages/$lesson_id"); ?>'/>
    </metadata>
  
      <organizations default="kompetenzSchmiedeOrg">
       <organization identifier="kompetenzSchmiedeOrg">
        <title><?php echo "SCORM Lesson from Campus Kompetenz Schmiede"; ?></title>
        <item identifier="<?php echo 'lesson_' . $lesson_id; ?>" identifierref="<?php echo 'lesson_' . $lesson_id; ?>">
              <title><?php echo $lesson_title; ?></title>
          </item>
       </organization>
     </organizations>

     <resources>
       <resource identifier="<?php echo 'lesson_' . $lesson_id; ?>" type="webcontent" adlcp:scormtype="sco" href="<?php echo 'lessons/' . $lesson_slug . '.html'; ?>">
          <file href="<?php echo 'lessons/' . $lesson_slug . '.html'; ?>"/>
          <!-- CSS Links -->
          <file href="../css/colors.min.css"/>
          <file href="../css/icons.min.css"/>
          <file href="../css/style.min.css"/>
          <file href="../css/custom.min.css"/>
          <!-- JS Links -->
          <file href="../js/jquery.min.js"/>
          <file href="../js/jquery-migrate.min.js"/>
          <file href="../js/jquery-query.min.js"/>
          <file href="../js/jquery-cookie.min.js"/>
          <file href="../js/jquery-scroll-to.min.js"/>
          <file href="../js/dialog.min.js"/>
          <file href="../js/elessens.min.js"/>
          <file href="../js/jquery.mask.js"/>
          <file href="../js/fitvids.js"/>
          <file href="../js/essens.js"/>
          <file href="../js/snc-script.min.js"/>
          <file href="../js/elcourse.min.js"/>
          <file href="../js/elwpf.min.js"/>
          <file href="../js/ecom-public.js"/>
          <!-- Images and Videos Links -->
           <?php 
           if( !empty($images_videos_files[$lesson_id]) ) {
             foreach ($images_videos_files[$lesson_id] as $file_source) {
             echo "<file href='$file_source'/>";
             }
           } ?>
          <!-- CDN Links -->
          <file href="https://fonts.googleapis.com/css?family=Open+Sans%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic&amp;display=swap&amp;ver=6.5.4"/>
          <file href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
        </resource>
     </resources>
    </manifest><?php
    return ob_get_clean();
  }

  private function create_html_file($content, $filename, $export_directory) {
        
    $file_path =  $export_directory . '/' . $filename;

    // Ensure the directory exists
    if (!file_exists(dirname($file_path))) {
        mkdir(dirname($file_path), 0755, true);
    }

    file_put_contents($file_path, $content);
  }

// Function to create SCORM package
public function create_scorm_package($source, $destination) {
  $zip = new \ZipArchive();
  if ($zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
      // Add manifest file without full path
      $zip->addFile($source . '/imsmanifest.xml', 'imsmanifest.xml');

      // Define folders to include in the ZIP
      $folders = ['lessons', 'css', 'js', 'images', 'videos', 'uncanny-snc'];

      foreach ($folders as $folder) {
          $folderPath = $source . '/' . $folder . '/';

          // Check if the folder exists to avoid errors
          if (is_dir($folderPath)) {
              $files = new \RecursiveIteratorIterator(
                  new \RecursiveDirectoryIterator($folderPath),
                  \RecursiveIteratorIterator::LEAVES_ONLY
              );

              foreach ($files as $name => $file) {
                  // Skip directories (handled automatically)
                  if (!$file->isDir()) {
                      // Get real and relative path for the current file
                      $filePath = $file->getRealPath();
                      // Calculate the path relative to the $source directory
                      $relativePath = substr($filePath, strlen($source) + 1);
                      
                      // Add file to the zip archive, preserving folder structure
                      $zip->addFile($filePath, $relativePath);
                  }
              }
          }
      }

      $zip->close();
    } else {
      wp_die('Failed to create ZIP archive.');
   }
 }

  private function delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!$this->delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
  }
  
} // End class UpdateHandler.
