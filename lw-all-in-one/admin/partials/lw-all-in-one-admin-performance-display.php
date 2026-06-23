<?php
/**
 * Performance Optimization Admin Display
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin/partials
 */

if (!defined('ABSPATH')) {
  exit;
}

$perf_webp = call_user_func($get_checkbox_value, $perf_fields, 'perf_webp');
$perf_critical_css = call_user_func($get_checkbox_value, $perf_fields, 'perf_critical_css');
$perf_defer_js = call_user_func($get_checkbox_value, $perf_fields, 'perf_defer_js');
$perf_remove_emoji = call_user_func($get_checkbox_value, $perf_fields, 'perf_remove_emoji');
$perf_disable_xmlrpc = call_user_func($get_checkbox_value, $perf_fields, 'perf_disable_xmlrpc');
$perf_limit_heartbeat = call_user_func($get_checkbox_value, $perf_fields, 'perf_limit_heartbeat');
$perf_remove_version = call_user_func($get_checkbox_value, $perf_fields, 'perf_remove_version');
$perf_remove_query_strings = call_user_func($get_checkbox_value, $perf_fields, 'perf_remove_query_strings');
$perf_preload = call_user_func($get_checkbox_value, $perf_fields, 'perf_preload');
$perf_dns_prefetch = call_user_func($get_checkbox_value, $perf_fields, 'perf_dns_prefetch');
$perf_lazy_load = call_user_func($get_checkbox_value, $perf_fields, 'perf_lazy_load');
$perf_minify_html = call_user_func($get_checkbox_value, $perf_fields, 'perf_minify_html');

$webp_quality = call_user_func($get_text_value, $perf_fields, 'webp_quality', '85');
$no_defer_scripts = call_user_func($get_text_value, $perf_fields, 'no_defer_scripts', '');
$preload_fonts = call_user_func($get_text_value, $perf_fields, 'preload_fonts', '');
$dns_prefetch_domains = call_user_func($get_text_value, $perf_fields, 'dns_prefetch_domains', '');
?>

<div class="wrap lw-aio-performance-wrap">
  <h1><?php esc_html_e('Performance Optimization', 'lw-all-in-one'); ?></h1>

  <div class="lw-aio-notice-container"></div>

  <div class="lw-aio-performance-grid">
    <!-- Cache Stats -->
    <div class="lw-aio-card lw-aio-stats-card">
      <h2><?php esc_html_e('Cache Statistics', 'lw-all-in-one'); ?></h2>
      <div class="lw-aio-stats-grid">
        <div class="lw-aio-stat-item">
          <span class="lw-aio-stat-label"><?php esc_html_e('WebP Images', 'lw-all-in-one'); ?></span>
          <span class="lw-aio-stat-value"><?php echo esc_html($cache_stats['webp_count']); ?></span>
        </div>
        <div class="lw-aio-stat-item">
          <span class="lw-aio-stat-label"><?php esc_html_e('WebP Size', 'lw-all-in-one'); ?></span>
          <span class="lw-aio-stat-value"><?php echo esc_html($cache_stats['webp_size']); ?></span>
        </div>
        <div class="lw-aio-stat-item">
          <span class="lw-aio-stat-label"><?php esc_html_e('Critical CSS Files', 'lw-all-in-one'); ?></span>
          <span class="lw-aio-stat-value"><?php echo esc_html($cache_stats['css_count']); ?></span>
        </div>
        <div class="lw-aio-stat-item">
          <span class="lw-aio-stat-label"><?php esc_html_e('Total Cache Size', 'lw-all-in-one'); ?></span>
          <span class="lw-aio-stat-value"><?php echo esc_html($cache_stats['total_size']); ?></span>
        </div>
      </div>
      <div class="lw-aio-cache-actions">
        <button type="button" class="button button-secondary lw-aio-clear-cache">
          <?php esc_html_e('Clear All Cache', 'lw-all-in-one'); ?>
        </button>
        <button type="button" class="button button-secondary lw-aio-regenerate-css">
          <?php esc_html_e('Regenerate Critical CSS', 'lw-all-in-one'); ?>
        </button>
      </div>
    </div>

    <!-- Settings Form -->
    <form method="post" action="options.php" class="lw-aio-settings-form">
      <?php settings_fields('lw_all_in_one_perf_settings'); ?>

      <!-- Image Optimization -->
      <div class="lw-aio-card">
        <h2><?php esc_html_e('Image Optimization', 'lw-all-in-one'); ?></h2>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_webp]" <?php checked($perf_webp, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Convert and Serve WebP Images', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Automatically convert images to WebP format and serve them to supported browsers. This can reduce image file sizes by 25-35%.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group lw-aio-indented">
          <label for="webp_quality">
            <?php esc_html_e('WebP Quality', 'lw-all-in-one'); ?>
          </label>
          <input type="number" id="webp_quality" name="lw_all_in_one[perf_fields][webp_quality]"
                 value="<?php echo esc_attr($webp_quality); ?>" min="50" max="100" step="5">
          <p class="description">
            <?php esc_html_e('Quality level for WebP conversion (50-100). Higher quality = larger file size.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_lazy_load]" <?php checked($perf_lazy_load, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Lazy Load Images', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Defer loading of images until they are about to enter the viewport. Improves initial page load time.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <?php if (!$gd_supported): ?>
        <div class="lw-aio-warning">
          <p>
            <strong><?php esc_html_e('Warning:', 'lw-all-in-one'); ?></strong>
            <?php esc_html_e('GD library is not available on your server. WebP conversion will not work.', 'lw-all-in-one'); ?>
          </p>
        </div>
        <?php endif; ?>
      </div>

      <!-- CSS Optimization -->
      <div class="lw-aio-card">
        <h2><?php esc_html_e('CSS Optimization', 'lw-all-in-one'); ?></h2>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_critical_css]" <?php checked($perf_critical_css, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Extract Critical CSS', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Extract and inline critical CSS for above-the-fold content. Non-critical CSS is loaded asynchronously.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_remove_query_strings]" <?php checked($perf_remove_query_strings, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Remove Query Strings from CSS/JS', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Remove version query strings from static resources to improve caching and CDN performance.', 'lw-all-in-one'); ?>
          </p>
        </div>
      </div>

      <!-- JavaScript Optimization -->
      <div class="lw-aio-card">
        <h2><?php esc_html_e('JavaScript Optimization', 'lw-all-in-one'); ?></h2>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_defer_js]" <?php checked($perf_defer_js, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Defer Non-Critical JavaScript', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Add defer attribute to non-critical JavaScript files to prevent render-blocking.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group lw-aio-indented">
          <label for="no_defer_scripts">
            <?php esc_html_e('Scripts to Exclude from Defer', 'lw-all-in-one'); ?>
          </label>
          <input type="text" id="no_defer_scripts" name="lw_all_in_one[perf_fields][no_defer_scripts]"
                 value="<?php echo esc_attr($no_defer_scripts); ?>" placeholder="jquery, custom-script">
          <p class="description">
            <?php esc_html_e('Comma-separated list of script handles that should not be deferred.', 'lw-all-in-one'); ?>
          </p>
        </div>
      </div>

      <!-- Resource Optimization -->
      <div class="lw-aio-card">
        <h2><?php esc_html_e('Resource Optimization', 'lw-all-in-one'); ?></h2>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_preload]" <?php checked($perf_preload, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Preload Critical Resources', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Preload critical fonts and resources to improve perceived performance.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group lw-aio-indented">
          <label for="preload_fonts">
            <?php esc_html_e('Fonts to Preload', 'lw-all-in-one'); ?>
          </label>
          <textarea id="preload_fonts" name="lw_all_in_one[perf_fields][preload_fonts]" rows="3"
                    placeholder="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap"><?php echo esc_textarea($preload_fonts); ?></textarea>
          <p class="description">
            <?php esc_html_e('One font URL per line.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_dns_prefetch]" <?php checked($perf_dns_prefetch, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('DNS Prefetch', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Add DNS prefetch for external domains to reduce connection time.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group lw-aio-indented">
          <label for="dns_prefetch_domains">
            <?php esc_html_e('Additional Domains', 'lw-all-in-one'); ?>
          </label>
          <textarea id="dns_prefetch_domains" name="lw_all_in_one[perf_fields][dns_prefetch_domains]" rows="3"
                    placeholder="cdn.example.com&#10;api.example.com"><?php echo esc_textarea($dns_prefetch_domains); ?></textarea>
          <p class="description">
            <?php esc_html_e('One domain per line (without protocol).', 'lw-all-in-one'); ?>
          </p>
        </div>
      </div>

      <!-- WordPress Optimization -->
      <div class="lw-aio-card">
        <h2><?php esc_html_e('WordPress Optimization', 'lw-all-in-one'); ?></h2>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_remove_emoji]" <?php checked($perf_remove_emoji, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Remove Emoji Support', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Disable WordPress emoji support to reduce HTTP requests and JavaScript execution.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_disable_xmlrpc]" <?php checked($perf_disable_xmlrpc, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Disable XML-RPC', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Disable XML-RPC to prevent DDoS attacks and reduce server load. Not needed for most sites.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_limit_heartbeat]" <?php checked($perf_limit_heartbeat, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Limit Heartbeat API', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Reduce heartbeat API frequency to decrease server load and improve performance.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_remove_version]" <?php checked($perf_remove_version, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Remove WordPress Version', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Remove WordPress version from source code for security and to reduce fingerprinting.', 'lw-all-in-one'); ?>
          </p>
        </div>

        <div class="lw-aio-form-group">
          <label class="lw-aio-toggle-label">
            <input type="checkbox" name="lw_all_in_one[perf_fields][perf_minify_html]" <?php checked($perf_minify_html, 'on'); ?>>
            <span class="lw-aio-toggle-slider"></span>
            <span class="lw-aio-toggle-text">
              <?php esc_html_e('Minify HTML Output', 'lw-all-in-one'); ?>
            </span>
          </label>
          <p class="description">
            <?php esc_html_e('Remove unnecessary whitespace and comments from HTML output to reduce page size.', 'lw-all-in-one'); ?>
          </p>
        </div>
      </div>

      <div class="lw-aio-form-actions">
        <?php submit_button(__('Save Settings', 'lw-all-in-one'), 'primary', 'submit', false); ?>
      </div>
    </form>
  </div>
</div>

<script>
jQuery(document).ready(function($) {
  // Clear cache
  $('.lw-aio-clear-cache').on('click', function() {
    var $button = $(this);
    $button.prop('disabled', true).text('<?php esc_html_e('Clearing...', 'lw-all-in-one'); ?>');

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'lw_all_in_one_clear_perf_cache',
        security: '<?php echo wp_create_nonce('lw_all_in_one'); ?>'
      },
      success: function(response) {
        if (response.success) {
          showNotice(response.data.message, 'success');
          location.reload();
        } else {
          showNotice(response.data.message || '<?php esc_html_e('Error clearing cache.', 'lw-all-in-one'); ?>', 'error');
        }
      },
      error: function() {
        showNotice('<?php esc_html_e('Error clearing cache.', 'lw-all-in-one'); ?>', 'error');
      },
      complete: function() {
        $button.prop('disabled', false).text('<?php esc_html_e('Clear All Cache', 'lw-all-in-one'); ?>');
      }
    });
  });

  // Regenerate critical CSS
  $('.lw-aio-regenerate-css').on('click', function() {
    var $button = $(this);
    $button.prop('disabled', true).text('<?php esc_html_e('Regenerating...', 'lw-all-in-one'); ?>');

    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action: 'lw_all_in_one_regenerate_critical_css',
        security: '<?php echo wp_create_nonce('lw_all_in_one'); ?>'
      },
      success: function(response) {
        if (response.success) {
          showNotice(response.data.message, 'success');
        } else {
          showNotice(response.data.message || '<?php esc_html_e('Error regenerating CSS.', 'lw-all-in-one'); ?>', 'error');
        }
      },
      error: function() {
        showNotice('<?php esc_html_e('Error regenerating CSS.', 'lw-all-in-one'); ?>', 'error');
      },
      complete: function() {
        $button.prop('disabled', false).text('<?php esc_html_e('Regenerate Critical CSS', 'lw-all-in-one'); ?>');
      }
    });
  });

  function showNotice(message, type) {
    var $notice = $('<div class="lw-aio-notice lw-aio-notice-' + type + '">' + message + '</div>');
    $('.lw-aio-notice-container').append($notice);

    setTimeout(function() {
      $notice.fadeOut(function() {
        $(this).remove();
      });
    }, 5000);
  }
});
</script>
