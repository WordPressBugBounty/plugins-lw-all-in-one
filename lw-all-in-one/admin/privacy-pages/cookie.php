<?php
$patterns = array();
$patterns[0] = '/replace_cookie_1/';
$patterns[1] = '/replace_cookie_2/';
$replacements = array();
$replacements[0] = $domain;
$replacements[1] = $date;
$cookie_file = wp_filter_post_kses(preg_replace($patterns, $replacements, $cookie_file));

if ($page_cookie->ID != '') {
  $cookie_page = array(
    'ID' => $page_cookie->ID,
    'post_content' => $cookie_file,
    'post_status' => 'publish',
  );
  $post_id = wp_update_post($cookie_page, true);
  if (is_wp_error($post_id)) {
    $errors = $post_id->get_error_messages();
    $create_pages_resposes['cookie-policy']['status'] = 'error';
    $create_pages_resposes['cookie-policy']['message'] = esc_attr__("cookie-policy could not be update!", 'lw_all_in_one');
    $create_pages_resposes['cookie-policy']['action'] = 'updated';
    $create_pages_resposes['cookie-policy']['post_id'] = $post_id;
  } else {
    $create_pages_resposes['cookie-policy']['status'] = 'success';
    $create_pages_resposes['cookie-policy']['message'] = esc_attr__("cookie-policy updated successfully!", 'lw_all_in_one');
    $create_pages_resposes['cookie-policy']['action'] = 'updated';
    $create_pages_resposes['cookie-policy']['post_id'] = $post_id;
  }

} else {
  $cookie_page = array(
    'post_title' => $post_title,
    'post_status' => 'publish',
    'post_type' => 'page',
    'post_content' => $cookie_file,
  );
  $post_id = wp_insert_post($cookie_page, true);
  if (is_wp_error($post_id)) {
    $create_pages_resposes['cookie-policy']['status'] = 'error';
    $create_pages_resposes['cookie-policy']['message'] = esc_attr__("cookie-policy could not be created!", 'lw_all_in_one');
    $create_pages_resposes['cookie-policy']['action'] = 'created';
    $create_pages_resposes['cookie-policy']['post_id'] = $post_id;
  } else {
    $create_pages_resposes['cookie-policy']['status'] = 'success';
    $create_pages_resposes['cookie-policy']['message'] = esc_attr__("cookie-policy created successfully!", 'lw_all_in_one');
    $create_pages_resposes['cookie-policy']['action'] = 'created';
    $create_pages_resposes['cookie-policy']['post_id'] = $post_id;
  }

}

if (is_plugin_active('wp-fastest-cache/wpFastestCache.php')) {
  if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
    $GLOBALS['wp_fastest_cache']->deleteCache(true);
  }
}