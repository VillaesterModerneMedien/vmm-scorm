<?php
/**
 * This class is about Template Pages Class.
 *
 * @author Moazzem Hossain and Monir Islam
 * @version 1.0.0
 * @package Ecom Product Customize
 */

namespace EcomHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Custom control for radio buttons with nested options.
 *
 * @package Ecom Helper
 */
class TemplatePages {

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

  /**
   * Hooks.
   */
  public function hooks() {

    // add_filter( 'page_template', [$this, 'ecom_custom_pages']);
    // add_filter( 'theme_page_templates', [$this, 'ecom_custom_pages_to_select'], 99, 4 );

  }

 public function ecom_custom_pages( $page_template ){

  $slug = get_page_template_slug();
  $template_path = ECOM_PATH . '/page-templates/';

  // if( $slug == 'product-customize.php' ) $page_template = $template_path . 'product-customize.php';

  if( $slug == 'front-page.php' ) $page_template = $template_path . 'front-page.php';

  if( $slug == 'hello-page.php' ) $page_template = $template_path . 'hello-page.php';

  // if( $slug == 'about-us.php' ) $page_template = $template_path . 'about-us.php';

  return $page_template;

 }

 public function ecom_custom_pages_to_select( $post_templates, $wp_theme, $post, $post_type ) {

    // Add custom template named template-custom.php to select dropdown
    // $post_templates['product-customize.php'] = __('Product Customize');
    $post_templates['front-page.php'] = __('Front Page');
    $post_templates['hello-page.php'] = __('Hello Page');
    // $post_templates['about-us.php'] = __('About Page');
    
    return $post_templates;

  }

} // End class