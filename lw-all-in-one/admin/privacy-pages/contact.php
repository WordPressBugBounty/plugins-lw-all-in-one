<?php

$patterns = array();
$patterns[0] = '/replace_contact_2/';
$replacements = array();
$replacements[0] = $date;
$contact_file = wp_filter_post_kses(preg_replace($patterns, $replacements, $contact_file));

if ($page_contact->ID != '') {
  $contact_page = array(
    'ID' => $page_contact->ID,
    'post_content' => $contact_file,
    'post_status' => 'publish',
  );
  $post_id = wp_update_post($contact_page, true);
  if (is_wp_error($post_id)) {
    $create_pages_resposes['informativa-trattamento-dati']['status'] = 'error';
    $create_pages_resposes['informativa-trattamento-dati']['message'] = esc_attr__("informativa-trattamento-dati could not be update!", 'lw_all_in_one');
    $create_pages_resposes['informativa-trattamento-dati']['action'] = 'updated';
    $create_pages_resposes['informativa-trattamento-dati']['post_id'] = $post_id;
  } else {
    $create_pages_resposes['informativa-trattamento-dati']['status'] = 'success';
    $create_pages_resposes['informativa-trattamento-dati']['message'] = esc_attr__("informativa-trattamento-dati updated successfully!", 'lw_all_in_one');
    $create_pages_resposes['informativa-trattamento-dati']['action'] = 'updated';
    $create_pages_resposes['informativa-trattamento-dati']['post_id'] = $post_id;
  }

} else {
  $contact_page = array(
    'post_title' => $post_title,
    'post_status' => 'publish',
    'post_type' => 'page',
    'post_content' => $contact_file,
  );
  $post_id = wp_insert_post($contact_page, true);
  if (is_wp_error($post_id)) {
    $create_pages_resposes['informativa-trattamento-dati']['status'] = 'error';
    $create_pages_resposes['informativa-trattamento-dati']['message'] = esc_attr__("informativa-trattamento-dati could not be created!", 'lw_all_in_one');
    $create_pages_resposes['informativa-trattamento-dati']['action'] = 'created';
    $create_pages_resposes['informativa-trattamento-dati']['post_id'] = $post_id;
  } else {
    $create_pages_resposes['informativa-trattamento-dati']['status'] = 'success';
    $create_pages_resposes['informativa-trattamento-dati']['message'] = esc_attr__("informativa-trattamento-dati created successfully!", 'lw_all_in_one');
    $create_pages_resposes['informativa-trattamento-dati']['action'] = 'created';
    $create_pages_resposes['informativa-trattamento-dati']['post_id'] = $post_id;
  }

}