<?php

if (!function_exists('clean_domain')) {
  function clean_domain($domain) {
    $clean = preg_replace('#^http(s)?://#', '', $domain);
    $clean = preg_replace('/^www\./', '', $clean);
    $clean_arr = explode("/", $clean);
    $clean = $clean_arr[0];
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
      "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
      "â€”", "â€“", ",", "<", ">", "/", "?", " ");
    $clean = str_replace($strip, "", wp_strip_all_tags($clean));
    $clean = (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower( mb_convert_encoding( $clean, 'UTF-8' ) );

	return strtolower($clean);
  }
}

if (!function_exists('lw_all_in_one_validate_tracking_id')) {
  function lw_all_in_one_validate_tracking_id($str) {
    return (bool) preg_match('/^UA-\d+-\d+$|^G-[a-zA-Z0-9]+$|^GTM-[a-zA-Z0-9]+$|^AW-[a-zA-Z0-9]+$|^DC-[a-zA-Z0-9]+$/i', strval($str));
  }
}

if (!function_exists('recursive_sanitize_array_object')) {
  function recursive_sanitize_array_object($input) {
    if (is_array($input) || is_object($input)) {
      foreach ($input as $key => &$value) {
        if (is_array($value) || is_object($value)) {
          $value = recursive_sanitize_array_object($value);
        } else {
          $value = sanitize_text_field($value);
        }
      }
    }
    return $input;
  }
}

if (!function_exists('lw_all_in_one_kses_extended')) {
  /**
   * Extended version of wp_kses that allows HTML, script, and style tags.
   * Used for header/footer scripts where we need to allow these tags.
   *
   * Allowed tags include:
   * - All standard post tags (p, div, span, a, img, etc.)
   * - script (with type, src, async, defer, etc.)
   * - style (with type, media, scoped, nonce)
   * - noscript
   * - meta (for Google site verification, Open Graph, charset, etc.)
   * - iframe (for embeds and tracking pixels)
   * - Event handlers (onclick, onload, onerror)
   * - Data attributes (data-*)
   *
   * @param string $content The content to sanitize
   * @return string Sanitized content with allowed tags
   */
  function lw_all_in_one_kses_extended($content) {
    // Get all allowed post tags
    $allowed_tags = wp_kses_allowed_html('post');

    // Add script tag with common attributes
    $allowed_tags['script'] = array(
      'type' => true,
      'src' => true,
      'async' => true,
      'defer' => true,
      'charset' => true,
      'crossorigin' => true,
      'integrity' => true,
      'nomodule' => true,
      'nonce' => true,
      'referrerpolicy' => true,
    );

    // Add style tag with common attributes
    $allowed_tags['style'] = array(
      'type' => true,
      'media' => true,
      'scoped' => true,
      'nonce' => true,
    );

    // Add noscript tag
    $allowed_tags['noscript'] = array();

    // Add meta tag (for Google site verification, Open Graph, etc.)
    $allowed_tags['meta'] = array(
      'name' => true,
      'content' => true,
      'property' => true,
      'charset' => true,
      'http-equiv' => true,
      'scheme' => true,
    );

    // Add iframe (often used with scripts like Google Analytics)
    $allowed_tags['iframe'] = array(
      'src' => true,
      'width' => true,
      'height' => true,
      'frameborder' => true,
      'allowfullscreen' => true,
      'allow' => true,
      'name' => true,
      'sandbox' => true,
      'srcdoc' => true,
      'loading' => true,
      'referrerpolicy' => true,
      'title' => true,
    );

    // Extend common tags to include data-* attributes and event handlers
    $global_attributes = array(
      'id' => true,
      'class' => true,
      'style' => true,
      'title' => true,
      'role' => true,
      'aria-label' => true,
      'aria-hidden' => true,
      'aria-describedby' => true,
      'aria-labelledby' => true,
      'data-*' => true,
      // Event handlers that might be needed for tracking scripts
      'onclick' => true,
      'onload' => true,
      'onerror' => true,
    );

    // Add global attributes to all existing tags
    foreach ($allowed_tags as $tag => $attributes) {
      if (is_array($attributes)) {
        $allowed_tags[$tag] = array_merge($attributes, $global_attributes);
      }
    }

    // Define allowed protocols for URLs
    $allowed_protocols = array('http', 'https', 'data', 'javascript');

    return wp_kses($content, $allowed_tags, $allowed_protocols);
  }
}