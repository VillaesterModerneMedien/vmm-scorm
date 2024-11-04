<?php
namespace VMMHelper;

if ( ! function_exists( 'vmm_get_filename_with_extension' ) ) {

 function vmm_get_filename_with_extension($url) {

    $path = parse_url($url, PHP_URL_PATH);
    $fileInfo = pathinfo($path);
     return $fileInfo['basename'];

  }

} // end conditon

if ( ! function_exists( 'vmm_replace_url_in_style' ) ) {

 function vmm_replace_url_in_style($style, $oldUrl, $newUrl) {
    return str_replace($oldUrl, $newUrl, $style);
 }

} // end conditon

if ( ! function_exists( 'vmm_extract_url_from_style' ) ) {

  function vmm_extract_url_from_style($style) {
    $pattern = '/url\((\'|")?(.*?)\1\)/';
    preg_match($pattern, $style, $matches);
    return $matches[2] ?? '';
}

} // end conditon

if ( ! function_exists( 'vmm_clear_directory' ) ) {

  function vmm_clear_directory($directory, $exclude = []) {
    $files = glob($directory . '/*'); // Get all file names

    foreach ($files as $file) { // Iterate files
        $basename = basename($file);

        // Skip the excluded folders or files
        if (in_array($basename, $exclude)) {
            continue;
        }

        if (is_file($file)) {
            unlink($file); // Delete file
        } elseif (is_dir($file)) {
            vmm_clear_directory($file, $exclude); // Recursively delete folder contents
            rmdir($file); // Delete the directory
        }
    }
  } // end function

} // end conditon

if (!function_exists('vmm_recursive_delete')) {
  function vmm_recursive_delete($dir) {
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

          if (!vmm_recursive_delete($dir . "/" . $item)) {
              chmod($dir . "/" . $item, 0777);
              if (!vmm_recursive_delete($dir . "/" . $item)) {
                  return false;
              }
          }
      }

      return rmdir($dir);
  }
}

if (!function_exists('vmm_recursive_copy')) {
  function vmm_recursive_copy($src, $dst) {
      $dir = opendir($src);
      @mkdir($dst, 0755, true); // Create the destination directory with correct permissions

      while (false !== ($file = readdir($dir))) {
          if (($file != '.') && ($file != '..')) {
              if (is_dir($src . '/' . $file)) {
                  // Recursively copy subdirectories
                  vmm_recursive_copy($src . '/' . $file, $dst . '/' . $file);
              } else {
                  // Copy files
                  copy($src . '/' . $file, $dst . '/' . $file);
              }
          }
      }
      closedir($dir);
  }
}

if (!function_exists('vmm_copy_directory')) {
  function vmm_copy_directory($source, $destination) {
      // Remove the existing directory if it exists
      if (file_exists($destination)) {
          vmm_recursive_delete($destination);
      }

      // Perform the copy operation
      vmm_recursive_copy($source, $destination);

  }
}

if ( ! function_exists( 'vmm_get_scorm_html' ) ) {

  function vmm_get_scorm_html($post_id, $post_content){

    $post_id = absint( $post_id );
    $_content = '';
    // Check if the post was built with Elementor
// Check if Elementor exists and is active
    if (defined('ELEMENTOR_VERSION') && class_exists('\Elementor\Plugin')) {
    // Use the correct namespace for Elementor
        if (\Elementor\Plugin::$instance->documents->get($post_id)->is_built_with_elementor()) {
            $_content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post_id);
        }
    }else{
     $_content = $post_content;
    }
    ob_start();
?><!doctype html>
<html lang="en-US">
 <head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo get_the_title($post_id); ?></title>

   <link rel="stylesheet" href="../css/colors.min.css">
   <link rel="stylesheet" href="../css/icons.min.css">
   <link rel="stylesheet" href="../css/style.min.css">
   <link rel="stylesheet" href="../css/custom.min.css">
   <script src="../js/jquery.min.js"></script>
   <script src="../js/jquery-migrate.min.js"></script>
   <script src="../js/jquery-query.min.js"></script>
   <script src="../js/jquery-cookie.min.js"></script>
   <script src="../js/jquery-scroll-to.min.js"></script>

 </head>

 <body onload="initializeScorm();" class="bp-nouveau page-template page-template-test-page-template page-template-test-page-template-php page page-id-<?php echo $post_id; ?> logged-in admin-bar no-customize-support wp-custom-logo buddyboss-theme bb-template-v2 buddypanel-logo-off  header-style-3  menu-style-standard elementor-default elementor-kit-5 elementor-page-4075 elementor-page-<?php echo $post_id; ?> no-js learndash-theme">

		<div id="page" class="site">
			<div id="content" class="site-content">
				<div class="container">
        <div class="bb-grid site-content-grid">
            <?php echo $_content; ?>

        </div><!-- .bb-grid -->
    </div><!-- .container -->
    </div><!-- #content -->

    </div><!-- #page -->
    <script>
    const vmmUrl='<?php echo esc_url( home_url( "/" ) ); ?>';
    const vmmPostId = '<?php echo $post_id; ?>';
    </script>

    <link rel="stylesheet" id="google-fonts-1-css" href="https://fonts.googleapis.com/css?family=Open+Sans%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic&amp;display=swap&amp;ver=6.5.4" type="text/css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="../js/dialog.min.js"></script>
    <script src="../js/elessens.min.js"></script>
    <script src="../js/jquery.mask.js"></script>
    <script src="../js/fitvids.js"></script>
    <script src="../js/essens.js"></script>
    <script src="../js/snc-script.min.js"></script>
    <script src="../js/elwpf.min.js"></script>
    <script src="../js/vmm-public.js"></script>
  </body>
</html><?php

  return ob_get_clean();

 } // end vmm_get_scorm_html() function

} // end conditon

if ( ! function_exists( 'vmm_convert_vc_sync_to_iframe' ) ) {

  function vmm_convert_vc_sync_to_iframe($lesson_id, $htmlContent){

    $dom = new \DOMDocument;

    // Load the HTML content into the DOMDocument
    @$dom->loadHTML($htmlContent, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

    $upload_dir = wp_upload_dir();
    // Initialize arrays to store image and video links
    $imageSources = [];
    $videoSources = [];
    $replacementLinks = [];
    $filteredContents = [];
    $images_videos_log = [];

    $elementsWithStyle = $dom->getElementsByTagName('*');
    $imgElements = $dom->getElementsByTagName('img');
    $videoElements = $dom->getElementsByTagName('video');
    $pElements = $dom->getElementsByTagName('p');

    foreach ($elementsWithStyle as $element) {
        if ($element->hasAttribute('style')) {
            $style = $element->getAttribute('style');
            $url = vmm_extract_url_from_style($style);
            if ($url) {
                $imageSources[] = $url;  // Add background image URL
            }
        }
    }

    foreach ($imgElements as $img) {
        $src = $img->getAttribute('src');
        $imageSources[] = $src;
    }

    foreach ($videoElements as $video) {
        $src = $video->getAttribute('src');
        $videoSources[] = $src;
    }

    if( is_array($imageSources) && count($imageSources) > 0 ) {

     foreach($imageSources as $image_source) {

      $image_filename = vmm_get_filename_with_extension($image_source);

      $image_export_path = $upload_dir['basedir'] . "/scorm_exports/images/$image_filename";

      $image_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $image_source);

      if( !file_exists( $image_export_path )) {

        copy($image_copy_path, $image_export_path);
        $replacementLinks[$image_source] = "../images/$image_filename";

        $images_videos_log[] = "../images/$image_filename";

      }

     }

    }

    if( is_array($videoSources) && count($videoSources) > 0 ) {

      foreach($videoSources as $video_source) {

       $video_filename = vmm_get_filename_with_extension($video_source);

       $video_export_path = $upload_dir['basedir'] . "/scorm_exports/videos/$video_filename";

       $video_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $video_source);

       if( !file_exists( $video_export_path )) {

         copy($video_copy_path, $video_export_path);
         $replacementLinks[$video_source] = "../videos/$video_filename";

         $images_videos_log[] = "../videos/$video_filename";

       }

      }

     }

    // Replace URLs in <img> elements
    foreach ($imgElements as $img) {
      $src = $img->getAttribute('src');
      if (isset($replacementLinks[$src])) {
          $img->setAttribute('src', $replacementLinks[$src]);
      }
    }

    // Replace URLs in <video> elements
    foreach ($videoElements as $video) {
      $src = $video->getAttribute('src');
      if (isset($replacementLinks[$src])) {
          $video->setAttribute('src', $replacementLinks[$src]);
      }
    }

    // Replace URLs in style attributes (e.g., background-image)
    foreach ($elementsWithStyle as $element) {
      if ($element->hasAttribute('style')) {
          $style = $element->getAttribute('style');
          $url = vmm_extract_url_from_style($style);
          if (isset($replacementLinks[$url])) {
              $newStyle = vmm_replace_url_in_style($style, $url, $replacementLinks[$url]);
              $element->setAttribute('style', $newStyle);
          }
      }
    }

  foreach ($pElements as $p) {
    $text = trim($p->textContent);
    if (strpos($text, '[vc_snc embed_type') === 0) {

      $shortcodes = preg_replace('/[\[\]]/', '', $p->textContent);
      $shortcode = str_replace(['″', '”'], '"', trim($shortcodes));

      $embedType = $itemId = $itemName = $width = $height = $frameborder = $src = '';

      // Use regular expressions to extract attributes
      preg_match('/embed_type="([^"]*)"/', $shortcode, $embedTypeMatch);
      preg_match('/item_id="([^"]*)"/', $shortcode, $itemIdMatch);
      preg_match('/item_name="([^"]*)"/', $shortcode, $itemNameMatch);
      preg_match('/width="([^"]*)"/', $shortcode, $widthMatch);
      preg_match('/height="([^"]*)"/', $shortcode, $heightMatch);
      preg_match('/frameborder="([^"]*)"/', $shortcode, $frameborderMatch);
      preg_match('/src="([^"]*)"/', $shortcode, $srcMatch);

      // Check if matches are found and assign values
      $embedType = $embedTypeMatch[1] ?? '';
      $itemId = $itemIdMatch[1] ?? '';
      $itemName = $itemNameMatch[1] ?? '';
      $width = $widthMatch[1] ?? '';
      $height = $heightMatch[1] ?? '';
      $frameborder = $frameborderMatch[1] ?? '';
      $src = $srcMatch[1] ?? '';

      if( $itemId ) {

        $copy_directory = $upload_dir['basedir'] . "/uncanny-snc/$itemId";
        $export_directory = $upload_dir['basedir'] . "/scorm_exports/uncanny-snc/$itemId";

        // Usage
        vmm_copy_directory($copy_directory, $export_directory);

      }

      if( $src ) {
        $pattern = '/^.*?\/uploads/';  // Matches everything up to and including 'uploads'
        $replace = '..';
        $src = preg_replace($pattern, $replace, $src);

      }

      // Create the iframe element with the extracted attributes
      $iframe = '<iframe src="' . htmlspecialchars($src) . '" width="' . htmlspecialchars($width) . '" height="' . htmlspecialchars($height) . '" frameborder="' . htmlspecialchars($frameborder) . '" title="' . htmlspecialchars($itemName) . '"></iframe>';

      // Replace the shortcode text with the iframe in the DOM
      $p->nodeValue = '';
      $iframeFragment = $dom->createDocumentFragment();
      $iframeFragment->appendXML($iframe);
      $p->appendChild($iframeFragment);
      $filteredContents[] = $shortcode;
    }
   }

    $new_html_content = $dom->saveHTML();
    return [$images_videos_log, $new_html_content];

  } // end function

} // end conditon
