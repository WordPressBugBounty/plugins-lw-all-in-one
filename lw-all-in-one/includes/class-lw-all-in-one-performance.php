<?php

/**
 * Performance Optimization Module
 *
 * Handles WebP image conversion, critical CSS, and other performance optimizations.
 *
 * @link       https://localweb.it/
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     Local Web S.R.L
 */

/**
 * Performance Optimization Class
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     Local Web S.R.L
 */
class Lw_All_In_One_Performance {

  /**
   * The loader that's responsible for maintaining and registering all hooks.
   *
   * @access   private
   * @var      Lw_All_In_One_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  private $loader;

  /**
   * The ID of this plugin.
   *
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Plugin options.
   *
   * @access   private
   * @var      array    $options    Plugin options.
   */
  private $options;

  /**
   * WebP cache directory.
   *
   * @access   private
   * @var      string    $webp_cache_dir    WebP cache directory path.
   */
  private $webp_cache_dir;

  /**
   * Critical CSS cache directory.
   *
   * @access   private
   * @var      string    $critical_css_cache_dir    Critical CSS cache directory path.
   */
  private $critical_css_cache_dir;

  /**
   * Initialize the class and set its properties.
   *
   * @param  string  $plugin_name       The name of the plugin.
   * @param  string  $version    The version of the plugin.
   * @param  object  $loader     The loader instance.
   */
  public function __construct($plugin_name, $version, $loader = null) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;
    $this->loader = $loader;
    $this->options = get_option($plugin_name);

    // Set cache directories
    $upload_dir = wp_upload_dir();
    $this->webp_cache_dir = $upload_dir['basedir'] . '/lw-aio-webp-cache/';
    $this->critical_css_cache_dir = $upload_dir['basedir'] . '/lw-aio-critical-css/';

    // Create cache directories if they don't exist
    $this->ensure_cache_directories();
  }

  /**
   * Ensure cache directories exist.
   *
   * @access   private
   */
  private function ensure_cache_directories() {
    if (!file_exists($this->webp_cache_dir)) {
      wp_mkdir_p($this->webp_cache_dir);
    }
    if (!file_exists($this->critical_css_cache_dir)) {
      wp_mkdir_p($this->critical_css_cache_dir);
    }
  }

  /**
   * Register all hooks for performance optimization.
   *
   * @access   public
   */
  public function register_hooks() {
    // WebP image handling
    if ($this->is_option_enabled('perf_webp')) {
      if ($this->loader) {
        $this->loader->add_filter('wp_get_attachment_image_src', $this, 'convert_image_src_to_webp', 10, 4);
        $this->loader->add_filter('image_downsize', $this, 'convert_image_downsize_to_webp', 10, 3);
        $this->loader->add_filter('wp_calculate_image_srcset', $this, 'convert_srcset_to_webp', 10, 5);
        $this->loader->add_filter('the_content', $this, 'convert_content_images_to_webp');
      } else {
        add_filter('wp_get_attachment_image_src', array($this, 'convert_image_src_to_webp'), 10, 4);
        add_filter('image_downsize', array($this, 'convert_image_downsize_to_webp'), 10, 3);
        add_filter('wp_calculate_image_srcset', array($this, 'convert_srcset_to_webp'), 10, 5);
        add_filter('the_content', array($this, 'convert_content_images_to_webp'));
      }
    }

    // Critical CSS
    if ($this->is_option_enabled('perf_critical_css')) {
      if ($this->loader) {
        $this->loader->add_action('wp_enqueue_scripts', $this, 'handle_critical_css', 999);
        $this->loader->add_action('wp_head', $this, 'output_critical_css', 1);
      } else {
        add_action('wp_enqueue_scripts', array($this, 'handle_critical_css'), 999);
        add_action('wp_head', array($this, 'output_critical_css'), 1);
      }
    }

    // JavaScript optimization
    if ($this->is_option_enabled('perf_defer_js')) {
      if ($this->loader) {
        $this->loader->add_filter('script_loader_tag', $this, 'defer_non_critical_js', 10, 3);
      } else {
        add_filter('script_loader_tag', array($this, 'defer_non_critical_js'), 10, 3);
      }
    }

    // Remove emoji support
    if ($this->is_option_enabled('perf_remove_emoji')) {
      remove_action('wp_head', 'print_emoji_detection_script', 7);
      remove_action('wp_print_styles', 'print_emoji_styles');
      remove_action('admin_print_scripts', 'print_emoji_detection_script');
      remove_action('admin_print_styles', 'print_emoji_styles');
      if ($this->loader) {
        $this->loader->add_filter('tiny_mce_plugins', $this, 'disable_emoji_tinymce');
        $this->loader->add_filter('wp_resource_hints', $this, 'disable_emoji_dns_prefetch', 10, 2);
      } else {
        add_filter('tiny_mce_plugins', array($this, 'disable_emoji_tinymce'));
        add_filter('wp_resource_hints', array($this, 'disable_emoji_dns_prefetch'), 10, 2);
      }
    }

    // Disable XML-RPC
    if ($this->is_option_enabled('perf_disable_xmlrpc')) {
      add_filter('xmlrpc_enabled', '__return_false');
      if ($this->loader) {
        $this->loader->add_filter('xmlrpc_methods', $this, 'disable_xmlrpc_pingback');
      } else {
        add_filter('xmlrpc_methods', array($this, 'disable_xmlrpc_pingback'));
      }
    }

    // Limit heartbeat API
    if ($this->is_option_enabled('perf_limit_heartbeat')) {
      if ($this->loader) {
        $this->loader->add_filter('heartbeat_send', $this, 'limit_heartbeat', 10, 2);
        $this->loader->add_filter('heartbeat_settings', $this, 'adjust_heartbeat_settings');
      } else {
        add_filter('heartbeat_send', array($this, 'limit_heartbeat'), 10, 2);
        add_filter('heartbeat_settings', array($this, 'adjust_heartbeat_settings'));
      }
    }

    // Remove WordPress version
    if ($this->is_option_enabled('perf_remove_version')) {
      remove_action('wp_head', 'wp_generator');
      add_filter('the_generator', '__return_empty_string');
    }

    // Remove query strings from static resources
    if ($this->is_option_enabled('perf_remove_query_strings')) {
      if ($this->loader) {
        $this->loader->add_filter('style_loader_src', $this, 'remove_query_strings', 10, 2);
        $this->loader->add_filter('script_loader_src', $this, 'remove_query_strings', 10, 2);
      } else {
        add_filter('style_loader_src', array($this, 'remove_query_strings'), 10, 2);
        add_filter('script_loader_src', array($this, 'remove_query_strings'), 10, 2);
      }
    }

    // Preload critical resources
    if ($this->is_option_enabled('perf_preload')) {
      if ($this->loader) {
        $this->loader->add_action('wp_head', $this, 'preload_critical_resources', 1);
      } else {
        add_action('wp_head', array($this, 'preload_critical_resources'), 1);
      }
    }

    // DNS prefetch for external domains
    if ($this->is_option_enabled('perf_dns_prefetch')) {
      if ($this->loader) {
        $this->loader->add_action('wp_head', $this, 'add_dns_prefetch', 1);
      } else {
        add_action('wp_head', array($this, 'add_dns_prefetch'), 1);
      }
    }

    // Lazy load images
    if ($this->is_option_enabled('perf_lazy_load')) {
      if ($this->loader) {
        $this->loader->add_filter('the_content', $this, 'add_lazy_loading_to_images');
        $this->loader->add_filter('post_thumbnail_html', $this, 'add_lazy_loading_to_images');
      } else {
        add_filter('the_content', array($this, 'add_lazy_loading_to_images'));
        add_filter('post_thumbnail_html', array($this, 'add_lazy_loading_to_images'));
      }
    }

    // Minify HTML
    if ($this->is_option_enabled('perf_minify_html')) {
      if ($this->loader) {
        $this->loader->add_action('template_redirect', $this, 'start_html_buffer', 1);
        $this->loader->add_action('wp_footer', $this, 'end_html_buffer', 999);
      } else {
        add_action('template_redirect', array($this, 'start_html_buffer'), 1);
        add_action('wp_footer', array($this, 'end_html_buffer'), 999);
      }
    }
  }

  /**
   * Check if an option is enabled.
   *
   * @access   private
   * @param    string    $option_key    Option key.
   * @return   bool                     Whether option is enabled.
   */
  private function is_option_enabled($option_key) {
    if (isset($this->options['perf_fields'][$option_key]) && $this->options['perf_fields'][$option_key] === 'on') {
      return true;
    }
    return false;
  }

  /**
   * Convert image source to WebP.
   *
   * @access   public
   * @param    array    $image         Image data.
   * @param    int      $attachment_id Attachment ID.
   * @param    string   $size          Image size.
   * @param    bool     $icon          Whether icon.
   * @return   array                   Modified image data.
   */
  public function convert_image_src_to_webp($image, $attachment_id, $size, $icon) {
    if (!$image || empty($image[0])) {
      return $image;
    }

    if ($this->should_serve_webp()) {
      $webp_url = $this->get_webp_url($image[0]);
      if ($webp_url) {
        $image[0] = $webp_url;
      }
    }

    return $image;
  }

  /**
   * Convert image downsize to WebP.
   *
   * @access   public
   * @param    bool|array    $false         False or image data.
   * @param    int           $attachment_id Attachment ID.
   * @param    string|array  $size          Image size.
   * @return   bool|array                   Modified image data.
   */
  public function convert_image_downsize_to_webp($false, $attachment_id, $size) {
    if (!$false || empty($false[0])) {
      return $false;
    }

    if ($this->should_serve_webp()) {
      $webp_url = $this->get_webp_url($false[0]);
      if ($webp_url) {
        $false[0] = $webp_url;
      }
    }

    return $false;
  }

  /**
   * Convert srcset to WebP.
   *
   * @access   public
   * @param    array    $sources       Image sources.
   * @param    array    $size_array    Size array.
   * @param    string   $image_src     Image source.
   * @param    array    $image_meta    Image metadata.
   * @param    int      $attachment_id Attachment ID.
   * @return   array                   Modified sources.
   */
  public function convert_srcset_to_webp($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    if (!$this->should_serve_webp()) {
      return $sources;
    }

    foreach ($sources as $width => $source) {
      $webp_url = $this->get_webp_url($source['url']);
      if ($webp_url) {
        $sources[$width]['url'] = $webp_url;
      }
    }

    return $sources;
  }

  /**
   * Convert content images to WebP.
   *
   * @access   public
   * @param    string    $content    Content.
   * @return   string                Modified content.
   */
  public function convert_content_images_to_webp($content) {
    if (!$this->should_serve_webp()) {
      return $content;
    }

    // Match img tags with src attribute
    $pattern = '/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i';

    $content = preg_replace_callback($pattern, function($matches) {
      $before_src = $matches[1];
      $src = $matches[2];
      $after_src = $matches[3];

      // Skip if already WebP or data URI
      if (preg_match('/\.webp($|\?)/i', $src) || strpos($src, 'data:') === 0) {
        return $matches[0];
      }

      // Skip external images
      if (!$this->is_local_image($src)) {
        return $matches[0];
      }

      $webp_url = $this->get_webp_url($src);
      if ($webp_url) {
        return '<img' . $before_src . 'src="' . esc_url($webp_url) . '"' . $after_src . '>';
      }

      return $matches[0];
    }, $content);

    // Also update background-image in style attributes
    $content = preg_replace_callback('/url\(["\']?([^"\')]+)["\']?\)/i', function($matches) {
      $url = $matches[1];

      // Skip if already WebP or data URI
      if (preg_match('/\.webp($|\?)/i', $url) || strpos($url, 'data:') === 0) {
        return $matches[0];
      }

      // Skip external images
      if (!$this->is_local_image($url)) {
        return $matches[0];
      }

      $webp_url = $this->get_webp_url($url);
      if ($webp_url) {
        return 'url("' . esc_url($webp_url) . '")';
      }

      return $matches[0];
    }, $content);

    return $content;
  }

  /**
   * Check if browser supports WebP.
   *
   * @access   private
   * @return   bool    Whether browser supports WebP.
   */
  private function should_serve_webp() {
    // Check if browser accepts WebP
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false) {
      return true;
    }

    // Check for WebP support cookie
    if (isset($_COOKIE['lw_webp_support']) && $_COOKIE['lw_webp_support'] === '1') {
      return true;
    }

    return false;
  }

  /**
   * Check if image is local.
   *
   * @access   private
   * @param    string    $url    Image URL.
   * @return   bool              Whether image is local.
   */
  private function is_local_image($url) {
    $site_url = site_url();
    $upload_url = wp_upload_dir()['baseurl'];

    return (strpos($url, $site_url) === 0 || strpos($url, $upload_url) === 0);
  }

  /**
   * Get WebP URL for an image.
   *
   * @access   private
   * @param    string    $image_url    Original image URL.
   * @return   string|false           WebP URL or false.
   */
  private function get_webp_url($image_url) {
    // Get the file path
    $image_path = $this->url_to_path($image_url);
    if (!$image_path || !file_exists($image_path)) {
      return false;
    }

    // Get file info
    $path_info = pathinfo($image_path);
    $extension = strtolower($path_info['extension']);

    // Skip non-image files
    if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
      return false;
    }

    // Generate WebP filename
    $webp_filename = $path_info['filename'] . '.webp';
    $webp_path = $this->webp_cache_dir . $webp_filename;

    // Create WebP if it doesn't exist
    if (!file_exists($webp_path)) {
      $this->create_webp($image_path, $webp_path, $extension);
    }

    // Return WebP URL
    if (file_exists($webp_path)) {
      $upload_url = wp_upload_dir()['baseurl'];
      return $upload_url . '/lw-aio-webp-cache/' . $webp_filename;
    }

    return false;
  }

  /**
   * Convert URL to file path.
   *
   * @access   private
   * @param    string    $url    URL.
   * @return   string|false       File path or false.
   */
  private function url_to_path($url) {
    // Remove query string
    $url = strtok($url, '?');

    // Get upload directory
    $upload_dir = wp_upload_dir();

    // Check if it's in uploads directory
    if (strpos($url, $upload_dir['baseurl']) === 0) {
      $relative_path = substr($url, strlen($upload_dir['baseurl']));
      return $upload_dir['basedir'] . $relative_path;
    }

    // Check if it's in wp-content
    $wp_content_url = content_url();
    if (strpos($url, $wp_content_url) === 0) {
      $relative_path = substr($url, strlen($wp_content_url));
      return WP_CONTENT_DIR . $relative_path;
    }

    // Try to get attachment ID and get path
    $attachment_id = attachment_url_to_postid($url);
    if ($attachment_id) {
      return get_attached_file($attachment_id);
    }

    return false;
  }

  /**
   * Create WebP image.
   *
   * @access   private
   * @param    string    $source_path    Source image path.
   * @param    string    $webp_path      WebP output path.
   * @param    string    $extension      Source file extension.
   * @return   bool                     Success status.
   */
  private function create_webp($source_path, $webp_path, $extension) {
    if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
      return false;
    }

    // Get image
    $image = imagecreatefromstring(file_get_contents($source_path));
    if (!$image) {
      return false;
    }

    // Create WebP
    $quality = 85; // Default quality
    if (isset($this->options['perf_fields']['webp_quality'])) {
      $quality = intval($this->options['perf_fields']['webp_quality']);
    }

    $result = imagewebp($image, $webp_path, $quality);
    imagedestroy($image);

    return $result;
  }

  /**
   * Handle critical CSS.
   *
   * @access   public
   */
  public function handle_critical_css() {
    global $wp_styles;

    // Get current page identifier
    $page_id = $this->get_page_identifier();

    // Check if critical CSS exists for this page
    $critical_css_file = $this->critical_css_cache_dir . $page_id . '.css';

    if (!file_exists($critical_css_file)) {
      // Generate critical CSS
      $this->generate_critical_css($page_id);
    }

    // Defer all CSS
    foreach ($wp_styles->queue as $handle) {
      if (isset($wp_styles->registered[$handle])) {
        $wp_styles->registered[$handle]->extra['after'] = array();
        $wp_styles->registered[$handle]->extra['conditional'] = '';
      }
    }
  }

  /**
   * Output critical CSS inline.
   *
   * @access   public
   */
  public function output_critical_css() {
    $page_id = $this->get_page_identifier();
    $critical_css_file = $this->critical_css_cache_dir . $page_id . '.css';

    if (file_exists($critical_css_file)) {
      echo '<style id="lw-critical-css" data-critical="true">';
      echo file_get_contents($critical_css_file); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo '</style>';
    }
  }

  /**
   * Get page identifier for caching.
   *
   * @access   private
   * @return   string    Page identifier.
   */
  private function get_page_identifier() {
    if (is_front_page()) {
      return 'front-page';
    }

    if (is_home()) {
      return 'home';
    }

    if (is_singular()) {
      return 'single-' . get_queried_object_id();
    }

    if (is_archive()) {
      $post_type = get_post_type();
      if (is_tax()) {
        $term = get_queried_object();
        return 'taxonomy-' . $term->taxonomy . '-' . $term->term_id;
      }
      return 'archive-' . $post_type;
    }

    if (is_404()) {
      return '404';
    }

    return 'default';
  }

  /**
   * Generate critical CSS for a page.
   *
   * @access   private
   * @param    string    $page_id    Page identifier.
   */
  private function generate_critical_css($page_id) {
    // This is a simplified version. For production, you would:
    // 1. Use a headless browser (Puppeteer/Playwright) to capture above-the-fold styles
    // 2. Or use a service like Critical CSS Generator
    // 3. Or manually define critical CSS for each template

    // For now, we'll create a placeholder that can be manually edited
    $critical_css = "/* Critical CSS for {$page_id} - Edit this file manually */\n";
    $critical_css .= "/* Above-the-fold styles go here */\n";

    file_put_contents($this->critical_css_cache_dir . $page_id . '.css', $critical_css);
  }

  /**
   * Defer non-critical JavaScript.
   *
   * @access   public
   * @param    string    $tag          Script tag.
   * @param    string    $handle       Script handle.
   * @param    string    $src          Script source.
   * @return   string                  Modified script tag.
   */
  public function defer_non_critical_js($tag, $handle, $src) {
    // Scripts that should not be deferred
    $no_defer = array(
      'jquery-core',
      'jquery',
      'jquery-migrate',
      'lw-all-in-one-public',
      'lw-all-in-one-consent',
    );

    // Add custom scripts to exclude from defer
    if (isset($this->options['perf_fields']['no_defer_scripts'])) {
      $custom_no_defer = explode(',', $this->options['perf_fields']['no_defer_scripts']);
      $no_defer = array_merge($no_defer, array_map('trim', $custom_no_defer));
    }

    if (in_array($handle, $no_defer)) {
      return $tag;
    }

    // Add defer attribute
    if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
      $tag = str_replace(' src', ' defer src', $tag);
    }

    return $tag;
  }

  /**
   * Disable emoji in TinyMCE.
   *
   * @access   public
   * @param    array    $plugins    TinyMCE plugins.
   * @return   array                Modified plugins.
   */
  public function disable_emoji_tinymce($plugins) {
    return array_diff($plugins, array('wpemoji'));
  }

  /**
   * Disable emoji DNS prefetch.
   *
   * @access   public
   * @param    array    $hints    Resource hints.
   * @param    string   $relation Relation type.
   * @return   array             Modified hints.
   */
  public function disable_emoji_dns_prefetch($hints, $relation) {
    if ($relation === 'dns-prefetch') {
      return array_filter($hints, function($hint) {
        return strpos($hint, 's.w.org') === false;
      });
    }
    return $hints;
  }

  /**
   * Disable XML-RPC pingback.
   *
   * @access   public
   * @param    array    $methods    XML-RPC methods.
   * @return   array                Modified methods.
   */
  public function disable_xmlrpc_pingback($methods) {
    unset($methods['pingback.ping']);
    unset($methods['pingback.extensions.getPingbacks']);
    return $methods;
  }

  /**
   * Limit heartbeat API data.
   *
   * @access   public
   * @param    array    $response    Heartbeat response.
   * @param    array    $data        Heartbeat data.
   * @return   array                 Modified response.
   */
  public function limit_heartbeat($response, $data) {
    // Remove unnecessary data from heartbeat
    if (isset($response['wp_autosave'])) {
      unset($response['wp_autosave']);
    }
    return $response;
  }

  /**
   * Adjust heartbeat settings.
   *
   * @access   public
   * @param    array    $settings    Heartbeat settings.
   * @return   array                Modified settings.
   */
  public function adjust_heartbeat_settings($settings) {
    // Increase interval to reduce server load
    $settings['interval'] = 60; // 60 seconds instead of default 15
    return $settings;
  }

  /**
   * Remove query strings from static resources.
   *
   * @access   public
   * @param    string    $src        Resource source.
   * @param    string    $handle     Resource handle.
   * @return   string                Modified source.
   */
  public function remove_query_strings($src, $handle) {
    if (strpos($src, '?') !== false) {
      // Keep version query string for WordPress core files
      if (strpos($src, 'wp-includes') === false && strpos($src, 'wp-admin') === false) {
        $src = strtok($src, '?');
      }
    }
    return $src;
  }

  /**
   * Preload critical resources.
   *
   * @access   public
   */
  public function preload_critical_resources() {
    // Preload critical fonts
    $fonts = array();
    if (isset($this->options['perf_fields']['preload_fonts'])) {
      $fonts = explode(',', $this->options['perf_fields']['preload_fonts']);
      $fonts = array_map('trim', $fonts);
    }

    foreach ($fonts as $font) {
      if (!empty($font)) {
        echo '<link rel="preload" as="font" href="' . esc_url($font) . '" crossorigin>';
      }
    }

    // Preload critical CSS
    $page_id = $this->get_page_identifier();
    $critical_css_file = $this->critical_css_cache_dir . $page_id . '.css';
    if (file_exists($critical_css_file)) {
      // Critical CSS is already inline, no need to preload
    }
  }

  /**
   * Add DNS prefetch for external domains.
   *
   * @access   public
   */
  public function add_dns_prefetch() {
    $domains = array(
      'www.google-analytics.com',
      'www.googletagmanager.com',
      'fonts.googleapis.com',
      'fonts.gstatic.com',
    );

    // Add custom domains
    if (isset($this->options['perf_fields']['dns_prefetch_domains'])) {
      $custom_domains = explode(',', $this->options['perf_fields']['dns_prefetch_domains']);
      $domains = array_merge($domains, array_map('trim', $custom_domains));
    }

    foreach ($domains as $domain) {
      if (!empty($domain)) {
        echo '<link rel="dns-prefetch" href="//' . esc_attr($domain) . '">';
      }
    }
  }

  /**
   * Add lazy loading to images.
   *
   * @access   public
   * @param    string    $content    Content.
   * @return   string                Modified content.
   */
  public function add_lazy_loading_to_images($content) {
    // WordPress 5.5+ has native lazy loading, so we only need to handle older versions
    // or add loading="eager" for above-the-fold images

    if (version_compare(get_bloginfo('version'), '5.5', '<')) {
      // Add loading="lazy" to images
      $content = preg_replace('/<img([^>]+)>/i', '<img$1 loading="lazy">', $content);
    }

    return $content;
  }

  /**
   * Start HTML buffer for minification.
   *
   * @access   public
   */
  public function start_html_buffer() {
    if (!is_admin() && !is_feed() && !is_preview()) {
      ob_start(array($this, 'minify_html'));
    }
  }

  /**
   * End HTML buffer.
   *
   * @access   public
   */
  public function end_html_buffer() {
    if (ob_get_level() > 0) {
      ob_end_flush();
    }
  }

  /**
   * Minify HTML.
   *
   * @access   public
   * @param    string    $html    HTML content.
   * @return   string            Minified HTML.
   */
  public function minify_html($html) {
    // Remove comments
    $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:.*?)-->/s', '', $html);

    // Remove whitespace between tags
    $html = preg_replace('/>\s+</', '><', $html);

    // Remove whitespace at start/end
    $html = trim($html);

    return $html;
  }

  /**
   * Clear WebP cache.
   *
   * @access   public
   */
  public function clear_webp_cache() {
    $files = glob($this->webp_cache_dir . '*.webp');
    if ($files) {
      foreach ($files as $file) {
        if (is_file($file)) {
          unlink($file);
        }
      }
    }
  }

  /**
   * Clear critical CSS cache.
   *
   * @access   public
   */
  public function clear_critical_css_cache() {
    $files = glob($this->critical_css_cache_dir . '*.css');
    if ($files) {
      foreach ($files as $file) {
        if (is_file($file)) {
          unlink($file);
        }
      }
    }
  }

  /**
   * Clear all performance caches.
   *
   * @access   public
   */
  public function clear_all_caches() {
    $this->clear_webp_cache();
    $this->clear_critical_css_cache();
  }

  /**
   * Get cache stats.
   *
   * @access   public
   * @return   array    Cache statistics.
   */
  public function get_cache_stats() {
    $webp_files = glob($this->webp_cache_dir . '*.webp');
    $css_files = glob($this->critical_css_cache_dir . '*.css');

    $webp_size = 0;
    if ($webp_files) {
      foreach ($webp_files as $file) {
        $webp_size += filesize($file);
      }
    }

    $css_size = 0;
    if ($css_files) {
      foreach ($css_files as $file) {
        $css_size += filesize($file);
      }
    }

    return array(
      'webp_count' => $webp_files ? count($webp_files) : 0,
      'webp_size' => size_format($webp_size),
      'css_count' => $css_files ? count($css_files) : 0,
      'css_size' => size_format($css_size),
      'total_size' => size_format($webp_size + $css_size),
    );
  }
}
