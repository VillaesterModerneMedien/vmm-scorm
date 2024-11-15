<?php
namespace VMMHelper;


if (!function_exists('vmm_clean_url')) {
    function vmm_clean_url($url)
    {
        // Remove query parameters
        $url_parts = explode('?', $url);
        return $url_parts[0];
    }
}

if (!function_exists('vmm_get_filename_with_extension')) {

    function vmm_get_filename_with_extension($url)
    {
        $url = vmm_clean_url($url);
        if (strpos($url, 'data:') === 0) {
            // Extract mime type
            preg_match('/data:(.*?);base64,/', $url, $matches);
            $mime = $matches[1];

            // Map mime types to extensions
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/svg+xml' => 'svg',
                'video/mp4' => 'mp4',
                'audio/mpeg' => 'mp3',
                'application/pdf' => 'pdf'
            ];

            $extension = $extensions[$mime] ?? 'bin';
            return 'file-' . uniqid() . '.' . $extension;
        }
        $path = parse_url($url, PHP_URL_PATH);
        $fileInfo = pathinfo($path);
        return $fileInfo['basename'];

    }

} // end conditon

if (!function_exists('vmm_replace_url_in_style')) {

    function vmm_replace_url_in_style($style, $oldUrl, $newUrl)
    {
        return str_replace($oldUrl, $newUrl, $style);
    }

} // end conditon

if (!function_exists('vmm_extract_url_from_style')) {
    function vmm_extract_url_from_style($style) {
        $urls = [];
        $patterns = [
            '/background-image:\s*url\([\'"]?(.*?)[\'"]?\)/',
            '/background:\s*.*?url\([\'"]?(.*?)[\'"]?\)/',
            '/url\([\'"]?(.*?)[\'"]?\)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $style, $matches)) {
                foreach ($matches[1] as $match) {
                    if (!empty($match)) {
                        $urls[] = $match;
                    }
                }
            }
        }
        return array_unique($urls);
    }
} // end conditon

if (!function_exists('process_media_files')) {
    function process_media_files($sources, $media_type, $upload_dir, &$replacementLinks, &$media_files_log)
    {
        if (is_array($sources) && count($sources) > 0) {
            foreach ($sources as $source) {
                $filename = vmm_get_filename_with_extension($source);
                $export_path = $upload_dir['basedir'] . "/scorm_exports/$media_type/$filename";
                $copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $source);

                if (!file_exists($export_path)) {
                    copy($copy_path, $export_path);
                    $replacementLinks[$source] = "../$media_type/$filename";
                    $media_files_log[] = "../$media_type/$filename";
                }
            }
        }
    }
}

if (!function_exists('vmm_clear_directory')) {

    function vmm_clear_directory($directory, $exclude = [])
    {
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
    function vmm_recursive_delete($dir)
    {
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
    function vmm_recursive_copy($src, $dst)
    {
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
    function vmm_copy_directory($source, $destination)
    {
        // Remove the existing directory if it exists
        if (file_exists($destination)) {
            vmm_recursive_delete($destination);
        }

        // Perform the copy operation
        vmm_recursive_copy($source, $destination);

    }
}

if ( !function_exists('vmm_get_scorm_html') ) {

function vmm_get_scorm_html($post_id, $post_content){

$post_id = absint($post_id);
$_content = '';
// Check if the post was built with Elementor
// Check if Elementor exists and is active
if (defined('ELEMENTOR_VERSION') && class_exists('\Elementor\Plugin')) {
    // Use the correct namespace for Elementor
    if (\Elementor\Plugin::$instance->documents->get($post_id)->is_built_with_elementor()) {
        $_content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post_id);
    }
} else {
    $_content = $post_content;
}
ob_start();
?><!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_the_title($post_id); ?></title>

    <link rel="stylesheet" href="../css/vmm-scorm.css">
    <script src="../js/vmm-scorm.js"></script>

</head>

<body class="bp-nouveau page-template page-template-test-page-template page-template-test-page-template-php page page-id-<?php echo $post_id; ?> logged-in admin-bar no-customize-support wp-custom-logo buddyboss-theme bb-template-v2 buddypanel-logo-off  header-style-3  menu-style-standard elementor-default elementor-kit-5 elementor-page-4075 elementor-page-<?php echo $post_id; ?> no-js learndash-theme">

<div id="page" class="site">
    <div id="content" class="site-content">
        <div class="container">
            <div class="bb-grid site-content-grid">
                <div class="content-area bb-grid-cell">
                    <main class="site-main">
                        <?php echo $_content; ?>
                    </main>
                </div><!-- .content-area -->
            </div><!-- .bb-grid -->
        </div><!-- .container -->
    </div><!-- #content -->
</div><!-- #page -->
<script>
    const vmmUrl = '<?php echo esc_url(home_url("/")); ?>';
    const vmmPostId = '<?php echo $post_id; ?>';
</script>

</body>
</html><?php

return ob_get_clean();

} // end vmm_get_scorm_html() function

} // end conditon

if (!function_exists('vmm_convert_vc_sync_to_iframe')) {

    function vmm_convert_vc_sync_to_iframe($lesson_id, $htmlContent) {
        ini_set('max_execution_time', 300); // 5 minutes
        ini_set('memory_limit', '512M');    // 512MB memory limit
        set_time_limit(300);                // Alternative way to set time limit
        $dom = new \DOMDocument;
        @$dom->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        
        $upload_dir = wp_upload_dir();
        $imageSources = [];
        $videoSources = [];
        $pdfSources = [];
        $replacementLinks = [];
        $filteredContents = [];
        $media_files_log = [];
        

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
            'http' => [
                'ignore_errors' => true
            ]
        ]);

        // Get all relevant elements
        $elementsWithStyle = $dom->getElementsByTagName('*');
        $imgElements = $dom->getElementsByTagName('img');
        $videoElements = $dom->getElementsByTagName('video');
        $pElements = $dom->getElementsByTagName('p');
        $linkElements = $dom->getElementsByTagName('a');
        $styleElements = $dom->getElementsByTagName('style');
        $iframes = $dom->getElementsByTagName('iframe');
        $body = $dom->getElementsByTagName('body')->item(0);
        $styles = $body->getElementsByTagName('link');
        $stylesArray = [];
        foreach ($styles as $style) {
            $stylesArray[] = $style;
        }

        // 1. Collect all URLs
        // Extract and replace background image URLs from style attributes
        foreach ($elementsWithStyle as $element) {
            if ($element->hasAttribute('style')) {
                $style = $element->getAttribute('style');
                $backgroundUrls = vmm_extract_url_from_style($style);
                foreach ($backgroundUrls as $url) {
                    $image_filename = vmm_get_filename_with_extension($url);
                    $image_export_path = $upload_dir['basedir'] . "/scorm_exports/images/$image_filename";
                    $image_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $url);

                    if (!file_exists($image_export_path)) {
                        copy($image_copy_path, $image_export_path, $context);
                        $media_files_log[] = "../images/$image_filename";
                    }

                    $style = str_replace($url, "../images/$image_filename", $style);
                }
                $element->setAttribute('style', $style);
            }
        }

        // Extract and replace background image URLs from style elements
        foreach ($styleElements as $styleElement) {
            $styleContent = $styleElement->nodeValue;
            $backgroundUrls = vmm_extract_url_from_style($styleContent);
            foreach ($backgroundUrls as $url) {
                $url = vmm_clean_url($url);
                $image_filename = vmm_get_filename_with_extension($url);
                $image_export_path = $upload_dir['basedir'] . "/scorm_exports/images/$image_filename";
                $image_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $url);

                if (!file_exists($image_export_path)) {
                    copy($image_copy_path, $image_export_path, $context);
                    $media_files_log[] = "../images/$image_filename";
                }

                $styleContent = str_replace($url, "../images/$image_filename", $styleContent);
            }
            $styleElement->nodeValue = $styleContent;
        }

        // Extract and replace image URLs
        foreach ($imgElements as $img) {
            $src = $img->getAttribute('src');
            $image_filename = vmm_get_filename_with_extension($src);
            $image_export_path = $upload_dir['basedir'] . "/scorm_exports/images/$image_filename";
            $image_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $src);

            if (!file_exists($image_export_path)) {
                copy($image_copy_path, $image_export_path, $context);
                $media_files_log[] = "../images/$image_filename";
            }

            $img->setAttribute('src', "../images/$image_filename");

            // Remove srcset and sizes attributes TODO: Add support for srcset and sizes
            $img->removeAttribute('srcset');
            $img->removeAttribute('sizes');
        }

        // Extract and replace video URLs
        foreach ($videoElements as $video) {
            $src = $video->getAttribute('src');
            $video_filename = vmm_get_filename_with_extension($src);
            $video_export_path = $upload_dir['basedir'] . "/scorm_exports/videos/$video_filename";
            $video_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $src);

            if (!file_exists($video_export_path)) {
                copy($video_copy_path, $video_export_path, $context);
                $media_files_log[] = "../videos/$video_filename";
            }

            $video->setAttribute('src', "../videos/$video_filename");
        }

        // Extract and replace PDF & lightbox images URLs
        foreach ($linkElements as $link) {
            $href = $link->getAttribute('href');
            if (is_string($href) && strpos($href, '.pdf') !== false) {
                $pdf_filename = vmm_get_filename_with_extension($href);
                $pdf_export_path = $upload_dir['basedir'] . "/scorm_exports/pdfs/$pdf_filename";
                $pdf_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $href);

                if (!file_exists($pdf_export_path)) {
                    copy($pdf_copy_path, $pdf_export_path, $context);
                    $media_files_log[] = "../pdfs/$pdf_filename";
                }

                // Immediately replace the href attribute
                $link->setAttribute('href', "../pdfs/$pdf_filename");
            }
            if ($link->hasAttribute('data-elementor-open-lightbox') &&
                $link->getAttribute('data-elementor-open-lightbox') === 'yes') {
                $href = $link->getAttribute('href');
                $image_filename = vmm_get_filename_with_extension($href);
                $image_export_path = $upload_dir['basedir'] . "/scorm_exports/images/$image_filename";
                $image_copy_path = preg_replace('/^.*?\/uploads/', $upload_dir['basedir'], $href);

                if (!file_exists($image_export_path)) {
                    copy($image_copy_path, $image_export_path, $context);
                    $media_files_log[] = "../images/$image_filename";
                }

                $link->setAttribute('href', "../images/$image_filename");
            }
        }

        // Replace the entire iframe processing loop with this simpler version
        foreach ($iframes as $iframe) {
            $src = $iframe->getAttribute('src');
            if ($src) {
                // Generate unique filename for this iframe
                $iframe_filename = 'iframe-' . uniqid() . '.html';
                $iframe_dir = $upload_dir['basedir'] . "/scorm_exports/iframes";

                // Create directory if it doesn't exist
                if (!file_exists($iframe_dir)) {
                    mkdir($iframe_dir, 0755, true);
                }

                // Download the HTML content
                $iframe_content = file_get_contents($src, false, $context);
                if ($iframe_content) {
                    // Save the HTML file
                    file_put_contents("$iframe_dir/$iframe_filename", $iframe_content);

                    // Update iframe src to point to the downloaded file
                    $iframe->setAttribute('src', "../iframes/$iframe_filename");
                }
            }
        }

        foreach ($stylesArray as $style) {
            if ($style->getAttribute('rel') === 'stylesheet') {
                $style_src = $style->getAttribute('href');
                if ($style_src) {
                    $style_content = file_get_contents($style_src, false, $context);
                    if ($style_content) {
                        $new_style = $dom->createElement('style');
                        $new_style->setAttribute('type', 'text/css');
                        $new_style->textContent = $style_content;
                        $style->parentNode->replaceChild($new_style, $style);
                    }
                }
            }
        }
        process_media_files($imageSources, 'images', $upload_dir, $replacementLinks, $media_files_log);
        process_media_files($videoSources, 'videos', $upload_dir, $replacementLinks, $media_files_log);
        process_media_files($pdfSources, 'pdfs', $upload_dir, $replacementLinks, $media_files_log);

        // Process vc_snc shortcodes
        foreach ($pElements as $p) {
            $text = trim($p->textContent);
            if (strpos($text, '[vc_snc embed_type') === 0) {
                $shortcodes = preg_replace('/[\[\]]/', '', $p->textContent);
                $shortcode = str_replace(['"', '"', '″', '"', '”'], '"', trim($shortcodes));
                $embedType = $itemId = $itemName = $width = $height = $frameborder = $src = '';

                preg_match('/embed_type="([^"]*)"/', $shortcode, $embedTypeMatch);
                preg_match('/item_id="(\d+)"/', $shortcode, $itemIdMatch);
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

                if ($itemId) {
                    $copy_directory = $upload_dir['basedir'] . "/uncanny-snc/$itemId";
                    $export_directory = $upload_dir['basedir'] . "/scorm_exports/uncanny-snc/$itemId";
                    vmm_copy_directory($copy_directory, $export_directory);
                }

                if ($src) {
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

        $dom = UIkitHelper::convertElementorToUIkit($dom);
        $new_html_content = $dom->saveHTML();

        return [$media_files_log, $new_html_content];
    }


} // end conditon
