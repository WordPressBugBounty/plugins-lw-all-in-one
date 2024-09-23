<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin/partials
 */
?>

<?php if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}?>

<div class="wrap">

  <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
  <hr>
  <?php settings_errors();?>
  <form method="post" name="<?php echo $this->plugin_name; ?>_options" id="<?php echo $this->plugin_name; ?>_options" action="options.php">

    <?php
      //Plugin options
      $options = get_option($this->plugin_name);
      $ga_activate = (isset($options['ga_activate'])) ? $options['ga_activate'] : '';
      $ga_fields_tracking_id = (isset($options['ga_fields']['tracking_id'])) ? esc_attr($options['ga_fields']['tracking_id']) : '';
      $ga_fields_save_ga_events = (isset($options['ga_fields']['save_ga_events'])) ? esc_attr($options['ga_fields']['save_ga_events']) : '';
      $ga_fields_monitor_email_link = (isset($options['ga_fields']['monitor_email_link'])) ? esc_attr($options['ga_fields']['monitor_email_link']) : '';
      $ga_fields_monitor_tel_link = (isset($options['ga_fields']['monitor_tel_link'])) ? esc_attr($options['ga_fields']['monitor_tel_link']) : '';
      $ga_fields_monitor_form_submit = (isset($options['ga_fields']['monitor_form_submit'])) ? esc_attr($options['ga_fields']['monitor_form_submit']) : '';
      $ga_fields_monitor_woocommerce_data = (isset($options['ga_fields']['monitor_woocommerce_data'])) ? esc_attr($options['ga_fields']['monitor_woocommerce_data']) : '';
      $wim_activate = (isset($options['wim_activate'])) ? esc_attr($options['wim_activate']) : '';
      $wim_fields_verification_status = (isset($options['wim_fields']['verification_status'])) ? esc_attr($options['wim_fields']['verification_status']) : '';
      $wim_fields_token = (isset($options['wim_fields']['token'])) ? esc_attr($options['wim_fields']['token']) : '';
      $wim_fields_rag_soc = (isset($options['wim_fields']['rag_soc'])) ? esc_attr($options['wim_fields']['rag_soc']) : '';
      $wim_fields_auto_show_wim = (isset($options['wim_fields']['auto_show_wim'])) ? esc_attr($options['wim_fields']['auto_show_wim']) : '';
      $wim_fields_show_wim_after = (isset($options['wim_fields']['show_wim_after'])) ? esc_attr($options['wim_fields']['show_wim_after']) : '';
      $wim_fields_show_mobile = (isset($options['wim_fields']['show_mobile'])) ? esc_attr($options['wim_fields']['show_mobile']) : '';
      $wim_fields_lingua = (isset($options['wim_fields']['lingua'])) ? esc_attr($options['wim_fields']['lingua']) : '';
      $wim_fields_messaggio_0 = (isset($options['wim_fields']['messaggio_0'])) ? esc_attr($options['wim_fields']['messaggio_0']) : '';
      $wim_fields_messaggio_1 = (isset($options['wim_fields']['messaggio_1'])) ? esc_attr($options['wim_fields']['messaggio_1']) : '';
      $cf7_activate = (isset($options['cf7_activate'])) ? esc_attr($options['cf7_activate']) : '';
      $lw_cf7_fields_save_cf7_subm = (isset($options['lw_cf7_fields']['save_cf7_subm'])) ? esc_attr($options['lw_cf7_fields']['save_cf7_subm']) : '';
      $lw_cf7_fields_opt_scr_deliv = (isset($options['lw_cf7_fields']['opt_scr_deliv'])) ? esc_attr($options['lw_cf7_fields']['opt_scr_deliv']) : '';
      $lw_cf7_fields_tipo_contratto = (isset($options['lw_cf7_fields']['tipo_contratto'])) ? esc_attr($options['lw_cf7_fields']['tipo_contratto']) : '';
      $lw_cf7_fields_id_contratto = (isset($options['lw_cf7_fields']['id_contratto'])) ? esc_attr($options['lw_cf7_fields']['id_contratto']) : '';
      $ck_activate = (isset($options['ck_activate'])) ? esc_attr($options['ck_activate']) : '';
      $ck_fields_banner_position = (isset($options['ck_fields']['banner_position'])) ? esc_attr($options['ck_fields']['banner_position']) : '';
      $ck_fields_ck_page_slug = (isset($options['ck_fields']['ck_page_slug'])) ? esc_attr($options['ck_fields']['ck_page_slug']) : '';
      $ck_fields_primary_color = (isset($options['ck_fields']['primary_color'])) ? esc_attr($options['ck_fields']['primary_color']) : '';
      $ck_fields_secondary_color = (isset($options['ck_fields']['secondary_color'])) ? esc_attr($options['ck_fields']['secondary_color']) : '';
      $ck_fields_heading_message = (isset($options['ck_fields']['heading_message'])) ? esc_attr($options['ck_fields']['heading_message']) : '';
      $ck_fields_gdpr_message = (isset($options['ck_fields']['gdpr_message'])) ? esc_textarea($options['ck_fields']['gdpr_message']) : '';
      $ck_fields_about_ck_message = (isset($options['ck_fields']['about_ck_message'])) ? esc_textarea($options['ck_fields']['about_ck_message']) : '';
      $lw_hf_fields_insert_header = (isset($options['lw_hf_fields']['insert_header'])) ? $options['lw_hf_fields']['insert_header'] : '';
      $lw_hf_fields_insert_footer = (isset($options['lw_hf_fields']['insert_footer'])) ? $options['lw_hf_fields']['insert_footer'] : '';
      $lw_aio_delete_data = (isset($options['lw_aio_fields']['delete_data'])) ? esc_attr($options['lw_aio_fields']['delete_data']) : '';
      $lw_aio_data_retention = (isset($options['lw_aio_fields']['data_retention'])) ? esc_attr($options['lw_aio_fields']['data_retention']) : '';

      settings_fields($this->plugin_name);
      do_settings_sections($this->plugin_name);

      if ($ga_activate === 'on') {
        $default_tab = 'tab_ga_events';
      } else if ($wim_activate === 'on') {
        $default_tab = 'tab_wim';
      } else if ($cf7_activate === 'on') {
        $default_tab = 'tab_cf7';
      } else if ($ck_activate === 'on') {
        $default_tab = 'tab_cookie_banner';
      } else {
        $default_tab = 'tab_hf';
      }
      $allowed_tabs = array('tab_ga_events', 'tab_wim', 'tab_cf7', 'tab_cookie_banner', 'tab_hf', 'tab_aio_options');
      $get_tab = isset($_GET['tab']) ? esc_attr(sanitize_text_field($_GET['tab'])) : '';
      $active_tab = (in_array($get_tab, $allowed_tabs)) ? $get_tab : $default_tab;
    ?>

    <div id="poststuff" class="lw-aio">
      <div id="post-body" class="metabox-holder columns-2">
        <!-- main content -->
        <div id="post-body-content">
          <div class="postbox">
            <div class="inside">
              <h2><?php esc_attr_e('Activate Services', 'lw_all_in_one');?></h2>
              <table class="lw-aio-settings-options">
                <tbody>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_activate]" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_ga_activate" <?php echo ($ga_activate === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_ga_activate">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Activate Google Analytics?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ck_activate]" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_ck_activate" <?php echo ($ck_activate === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_ck_activate">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Activate Cookie Consent Banner?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[wim_activate]" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_wim_activate" <?php echo ($wim_activate === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_wim_activate">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Activate Web Instant Messenger?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[cf7_activate]" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_cf7_activate" <?php echo ($cf7_activate === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_cf7_activate">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Activate LocalWeb Contact Form 7?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div id="post-body-content">
          <div class="inside">
            <h2 class="nav-tab-wrapper">

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_ga_events" class="nav-tab <?php echo $active_tab == 'tab_ga_events' ? 'nav-tab-active' : ''; ?><?php echo $ga_activate != 'on' ? ' d-none' : ''; ?>"><?php esc_attr_e('Google Analytics', 'lw_all_in_one');?></a>

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_cookie_banner" class="nav-tab <?php echo $active_tab == 'tab_cookie_banner' ? 'nav-tab-active' : ''; ?><?php echo $ck_activate != 'on' ? ' d-none' : ''; ?>"><?php esc_attr_e('Cookie Banner', 'lw_all_in_one');?></a>

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_wim" class="nav-tab <?php echo $active_tab == 'tab_wim' ? 'nav-tab-active' : ''; ?><?php echo $wim_activate != 'on' ? ' d-none' : ''; ?>"><?php esc_attr_e('Web Instant Messenger', 'lw_all_in_one');?></a>

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_cf7" class="nav-tab <?php echo $active_tab == 'tab_cf7' ? 'nav-tab-active' : ''; ?><?php echo $cf7_activate != 'on' ? ' d-none' : ''; ?>"><?php esc_attr_e('LocalWeb Contact Form 7', 'lw_all_in_one');?></a>

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_hf" class="nav-tab <?php echo $active_tab == 'tab_hf' ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e('Header/Footer Scripts', 'lw_all_in_one');?></a>

              <a href="?page=<?php echo $this->plugin_name; ?>&tab=tab_aio_options" class="nav-tab <?php echo $active_tab == 'tab_aio_options' ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e('Plugin Options', 'lw_all_in_one');?></a>

            </h2>
            <div id="tab_ga_events" class="tab-content<?php echo $active_tab != 'tab_ga_events' ? ' d-none' : ''; ?>">
              <div id="col-container">
                <div class="col-left">
			            <div class="col-wrap">
                    <table id="ga_events_table" class="lw-aio-settings-options<?php echo $ga_activate != 'on' ? ' d-none' : ''; ?>">
                      <tbody>
                        <tr>
                          <td colspan="2"><h2><?php esc_attr_e('Google Analytics Options', 'lw_all_in_one');?></h2></td>
                        </tr>
                        <tr>
                          <td class="lw-aio-settings-title">
                            <label for="ga_tracking_id">Tracking ID: </label>
                          </td>
                          <td>
                            <input type="text" id="ga_tracking_id" name="<?php echo $this->plugin_name; ?>[ga_fields][tracking_id]" <?php echo ($ga_fields_tracking_id !== '') ? 'value="'.$ga_fields_tracking_id.'"' : '';?> placeholder="UA-XXX / G-XXX / GTM-XXX" size="25">
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="lw-aio-settings-title">
                            <div class="button-secondary lw-aio-settings-custom-switch">
                              <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_fields][save_ga_events]" class="lw-aio-settings-custom-switch-checkbox" id="save_ga_events" <?php echo ($ga_fields_save_ga_events === 'on') ? 'checked="checked"' : '';?>>
                              <label class="lw-aio-settings-custom-switch-label" for="save_ga_events">
                                <div class="lw-aio-settings-custom-switch-inner"></div>
                                <div class="lw-aio-settings-custom-switch-switch"></div>
                              </label>
                            </div>
                            <div class="switch-desc"> <?php esc_attr_e('Save Google Analytics events locally on the database?', 'lw_all_in_one');?></div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="lw-aio-settings-title">
                            <div class="button-secondary lw-aio-settings-custom-switch">
                              <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_fields][monitor_email_link]" class="lw-aio-settings-custom-switch-checkbox" id="monitor_email_link" <?php echo ($ga_fields_monitor_email_link === 'on') ? 'checked="checked"' : '';?>>
                              <label class="lw-aio-settings-custom-switch-label" for="monitor_email_link">
                                <div class="lw-aio-settings-custom-switch-inner"></div>
                                <div class="lw-aio-settings-custom-switch-switch"></div>
                              </label>
                            </div>
                            <div class="switch-desc"> <?php esc_attr_e('Track Email link clicks', 'lw_all_in_one');?></div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="lw-aio-settings-title">
                            <div class="button-secondary lw-aio-settings-custom-switch">
                              <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_fields][monitor_tel_link]" class="lw-aio-settings-custom-switch-checkbox" id="monitor_tel_link" <?php echo ($ga_fields_monitor_tel_link === 'on') ? 'checked="checked"' : '';?>>
                              <label class="lw-aio-settings-custom-switch-label" for="monitor_tel_link">
                                <div class="lw-aio-settings-custom-switch-inner"></div>
                                <div class="lw-aio-settings-custom-switch-switch"></div>
                              </label>
                            </div>
                            <div class="switch-desc"> <?php esc_attr_e('Track Telephone link clicks', 'lw_all_in_one');?></div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="lw-aio-settings-title">
                            <div class="button-secondary lw-aio-settings-custom-switch">
                              <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_fields][monitor_form_submit]" class="lw-aio-settings-custom-switch-checkbox" id="monitor_form_submit" <?php echo ($ga_fields_monitor_form_submit === 'on') ? 'checked="checked"' : '';?>>
                              <label class="lw-aio-settings-custom-switch-label" for="monitor_form_submit">
                                <div class="lw-aio-settings-custom-switch-inner"></div>
                                <div class="lw-aio-settings-custom-switch-switch"></div>
                              </label>
                            </div>
                            <div class="switch-desc"> <?php esc_attr_e('Track Contact Form submission', 'lw_all_in_one');?></div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="lw-aio-settings-title">
                            <div class="button-secondary lw-aio-settings-custom-switch">
                              <input type="checkbox" name="<?php echo $this->plugin_name; ?>[ga_fields][monitor_woocommerce_data]" class="lw-aio-settings-custom-switch-checkbox" id="monitor_woocommerce_data" <?php echo ($ga_fields_monitor_woocommerce_data === 'on') ? 'checked="checked"' : '';?>>
                              <label class="lw-aio-settings-custom-switch-label" for="monitor_woocommerce_data">
                                <div class="lw-aio-settings-custom-switch-inner"></div>
                                <div class="lw-aio-settings-custom-switch-switch"></div>
                              </label>
                            </div>
                            <div class="switch-desc"> <?php esc_attr_e('Track WooCommerce data', 'lw_all_in_one');?></div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div id="col-right">
                  <div class="col-wrap">
                  </div>
                </div>
              </div>
            </div>
            <div id="tab_cookie_banner" class="tab-content<?php echo $active_tab != 'tab_cookie_banner' ? ' d-none' : ''; ?>">
              <div class="col-container<?php echo $ck_activate != 'on' ? ' d-none' : ''; ?>">
                <div class="col-left">
                  <div class="col-wrap">
                    <table class="lw-aio-settings-options">
                      <tbody>
                        <tr>
			                    <th scope="row">
                            <label for="banner_position"><?php esc_attr_e( 'Banner Position', 'lw_all_in_one'); ?></label>
                          </th>
                          <td>
                            <select name="<?php echo $this->plugin_name; ?>[ck_fields][banner_position]" id="banner_position">
                              <option value=""></option>
                              <option value="top" <?php selected( $ck_fields_banner_position, 'top' ); ?>><?php esc_attr_e( 'Top', 'lw_all_in_one'); ?></option>
                              <option value="bottom" <?php selected( $ck_fields_banner_position, 'bottom' ); ?>><?php esc_attr_e( 'Bottom', 'lw_all_in_one'); ?></option>
                            </select>
                          </td>
                        </tr>
                        <tr>
			                    <th scope="row">
                            <label for="ck_page_slug"><?php esc_attr_e( 'Cookie Page', 'lw_all_in_one'); ?></label>
                          </th>
                          <td>
                            <?php
                              $args = array(
                                'post_type' => 'page',
                                'post_status' => 'publish',
                                'posts_per_page' => -1,
                                // 'orderby' => 'title',
                                // 'order' => 'ASC',
                                // 's' => 'cookie'
                              );
                              $pages = get_posts($args);
                            ?>
                            <?php if (empty($pages)): ?>
                              <p><?php esc_attr_e('No pages found with "cookie" in the title. Go create one.', 'lw_all_in_one'); ?> <a href="<?php echo admin_url('admin.php?page=lw_all_in_one_privacy_policy'); ?>"><?php esc_attr_e('Privacy Pages', 'lw_all_in_one'); ?></a></p>
                            <?php else: ?>
                            <select name="<?php echo $this->plugin_name; ?>[ck_fields][ck_page_slug]" id="ck_page_slug">
                                <option value=""></option>
                              <?php foreach ($pages as $page) { ?>
                                <option value="<?php echo $page->post_name; ?>" <?php selected( $ck_fields_ck_page_slug, $page->post_name ); ?>><?php echo $page->post_title; ?></option>
                              <?php } ?>
                            </select>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <tr>
			                    <th scope="row">
                            <label for="primary_color"><?php esc_attr_e( 'Primary Banner Color', 'lw_all_in_one'); ?></label>
                          </th>
                          <td>
                            <input type="text" id="primary_color" name="<?php echo $this->plugin_name; ?>[ck_fields][primary_color]" <?php echo ($ck_fields_primary_color !== '') ? 'value="'.$ck_fields_primary_color.'"' : '';?> class="lwaio-color-picker">
                          </td>
                        </tr>
                        <tr>
			                    <th scope="row">
                            <label for="secondary_color"><?php esc_attr_e( 'Secondary Banner Color', 'lw_all_in_one'); ?></label>
                          </th>
                          <td>
                            <input type="text" id="secondary_color" name="<?php echo $this->plugin_name; ?>[ck_fields][secondary_color]" <?php echo ($ck_fields_secondary_color !== '') ? 'value="'.$ck_fields_secondary_color.'"' : '';?> class="lwaio-color-picker">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-right">
                  <div class="col-wrap">
                    <table class="lw-aio-settings-options">
                      <tbody>
                        <tr>
                          <td>
                            <h3 class="shfs-labels" for="heading_message"><?php esc_attr_e( 'Heading Message', 'lw_all_in_one'); ?></h3>
                            <input type="text" id="heading_message" name="<?php echo $this->plugin_name; ?>[ck_fields][heading_message]" <?php echo ($ck_fields_heading_message !== '') ? 'value="'.$ck_fields_heading_message.'"' : '';?> placeholder="<?php esc_attr_e('This site uses Cookies!', 'lw_all_in_one');?>" class="large-text">
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <h3 class="shfs-labels" for="gdpr_message"><?php esc_attr_e( 'GDPR Message', 'lw_all_in_one'); ?></h3>
                            <textarea rows="3" class="large-text" id="gdpr_message" name="<?php echo $this->plugin_name; ?>[ck_fields][gdpr_message]"><?php esc_attr_e($ck_fields_gdpr_message); ?></textarea>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <h3 class="shfs-labels" for="about_ck_message"><?php esc_attr_e( 'About Cookies Message', 'lw_all_in_one'); ?></h3>
                            <textarea rows="3" class="large-text" id="about_ck_message" name="<?php echo $this->plugin_name; ?>[ck_fields][about_ck_message]"><?php esc_attr_e($ck_fields_about_ck_message); ?></textarea>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div id="tab_wim" class="tab-content<?php echo $active_tab != 'tab_wim' ? ' d-none' : ''; ?>">
              <table class="lw-aio-settings-options<?php echo $wim_activate != 'on' ? ' d-none' : ''; ?>">
                <tbody>
                  <tr>
                    <td colspan="2"><h2><?php esc_attr_e('Web Instant Messenger Options', 'lw_all_in_one');?></h2></td>
                  </tr>
                  <?php if($wim_fields_verification_status == 'verified' && strlen($wim_fields_token) == 32) : ?>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="rag_soc"><?php esc_attr_e('Business Name', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <input type="text" id="rag_soc" name="<?php echo $this->plugin_name; ?>[wim_fields][rag_soc]" value="<?php echo ($wim_fields_rag_soc != '') ? $wim_fields_rag_soc : substr(get_option('blogname'), 0, 16) . '...';?>" maxlength="20">
                    </td>
                    <td>
                      <?php esc_attr_e('Insert here your business name which will appear on the chat header', 'lw_all_in_one');?>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="auto_show_wim"><?php esc_attr_e('Auto show WIM', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <select name="<?php echo $this->plugin_name; ?>[wim_fields][auto_show_wim]" id="auto_show_wim">
                          <option value="SI" <?php selected($wim_fields_auto_show_wim, 'SI', TRUE ); ?>><?php esc_attr_e('YES', 'lw_all_in_one');?></option>
                          <option value="NO" <?php selected($wim_fields_auto_show_wim, 'NO', TRUE ); ?>><?php esc_attr_e('NO', 'lw_all_in_one');?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="show_wim_after"><?php esc_attr_e('Auto show WIM after', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <select id="show_wim_after" name="<?php echo $this->plugin_name;?>[wim_fields][show_wim_after]">
                          <option value="5" <?php selected( $wim_fields_show_wim_after, '5', TRUE ); ?>><?php _e('5s', 'lw_all_in_one');?></option>
                          <option value="10" <?php selected( $wim_fields_show_wim_after, '10', TRUE ); ?>><?php _e('10s', 'lw_all_in_one');?></option>
                          <option value="20" <?php selected( $wim_fields_show_wim_after, '20', TRUE ); ?>><?php _e('20s', 'lw_all_in_one');?></option>
                          <option value="30" <?php selected( $wim_fields_show_wim_after, '30', TRUE ); ?>><?php _e('30s', 'lw_all_in_one');?></option>
                          <option value="45" <?php selected( $wim_fields_show_wim_after, '45', TRUE ); ?>><?php _e('45s', 'lw_all_in_one');?></option>
                          <option value="60" <?php selected( $wim_fields_show_wim_after, '60', TRUE ); ?>><?php _e('60s', 'lw_all_in_one');?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="show_mobile"><?php esc_attr_e('Show On Mobile', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <select id="show_mobile" name="<?php echo $this->plugin_name;?>[wim_fields][show_mobile]">
                          <option value="SI" <?php selected( $wim_fields_show_mobile, 'SI', TRUE ); ?>><?php _e('YES', 'lw_all_in_one');?></option>
                          <option value="NO" <?php selected( $wim_fields_show_mobile, 'NO', TRUE ); ?>><?php _e('NO', 'lw_all_in_one');?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="lingua"><?php esc_attr_e('Language', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <select id="lingua" name="<?php echo $this->plugin_name;?>[wim_fields][lingua]">
                        <option value="it" <?php selected( $wim_fields_lingua, 'it', TRUE ); ?>><?php _e('IT', 'lw_all_in_one');?></option>
                        <option value="en" <?php selected( $wim_fields_lingua, 'en', TRUE ); ?>><?php _e('EN', 'lw_all_in_one');?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="messaggio_0"><?php esc_attr_e('Automatic Message 0', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <textarea id="messaggio_0" name="<?php echo $this->plugin_name;?>[wim_fields][messaggio_0]" maxlength="250" cols="55" rows="3" class=""><?php echo ($wim_fields_messaggio_0 != '') ? $wim_fields_messaggio_0 : 'Salve! Come posso esserle utile?';?></textarea>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title-wim">
                      <label for="messaggio_1"><?php esc_attr_e('Automatic Message 1', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field-wim">
                      <textarea id="messaggio_1" name="<?php echo $this->plugin_name;?>[wim_fields][messaggio_1]" maxlength="250" cols="55" rows="3" class=""><?php echo ($wim_fields_messaggio_1 != '') ? $wim_fields_messaggio_1 : 'Gentilmente, mi può lasciare un contatto telefonico o email in modo da poterla eventualmente ricontattare?';?></textarea>

                      <input type="hidden" name="<?php echo $this->plugin_name;?>[wim_fields][token]" value="<?php echo ($wim_fields_token != '') ? $wim_fields_token : '';?>"/>
                      <input type="hidden" name="<?php echo $this->plugin_name;?>[wim_fields][verification_status]" value="<?php echo ($wim_fields_verification_status != '') ? $wim_fields_verification_status : '';?>" />
                      <input type="hidden" name="<?php echo $this->plugin_name;?>[wim_fields][save_wim_options]" />
                    </td>
                  </tr>
                  <?php else: ?>
                  <tr>
                    <td>
                      <div id="verification_status_response"></div>
                        <input type="hidden" id="wim_fields_verification_status_resp" value="" name="<?php echo $this->plugin_name;?>[wim_fields][verification_status]"/>
                        <input type="hidden" id="wim_fields_token_resp" value="" name="<?php echo $this->plugin_name;?>[wim_fields][token]"/>
                        <input type="hidden" value="<?php echo substr(get_option('blogname'), 0, 16) . '...'; ?>" name="<?php echo $this->plugin_name;?>[wim_fields][rag_soc]"/>
                        <input type="hidden" id="wim_fields_auto_show_wim" value="" name="<?php echo $this->plugin_name;?>[wim_fields][auto_show_wim]"/>
                        <input type="hidden" id="wim_fields_show_wim_after" value="" name="<?php echo $this->plugin_name;?>[wim_fields][show_wim_after]"/>
                        <input type="hidden" id="wim_fields_show_mobile" value="" name="<?php echo $this->plugin_name;?>[wim_fields][show_mobile]"/>
                        <input type="hidden" id="wim_fields_lingua" value="" name="<?php echo $this->plugin_name;?>[wim_fields][lingua]"/>
                        <input type="hidden" id="wim_fields_messaggio_0" value="" name="<?php echo $this->plugin_name;?>[wim_fields][messaggio_0]"/>
                        <input type="hidden" id="wim_fields_messaggio_1" value="" name="<?php echo $this->plugin_name;?>[wim_fields][messaggio_1]"/>
                        <?php if($wim_activate === 'on'): ;?>
                          <input type="hidden" name="<?php echo $this->plugin_name;?>[wim_fields][save_wim_options]"/>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <tr><td colspan="2"><?php submit_button(__('Verify Activation', 'lw_all_in_one'), 'secondary', 'wim_verify_attivation', TRUE);?></td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div id="tab_cf7" class="tab-content<?php echo $active_tab != 'tab_cf7' ? ' d-none' : ''; ?>">
              <table class="lw-aio-settings-options<?php echo $cf7_activate != 'on' ? ' d-none' : ''; ?>">
                <tbody>
                  <tr>
                    <td colspan="2"><h2><?php esc_attr_e('Contact Form 7 Addon Options', 'lw_all_in_one');?></h2></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[lw_cf7_fields][opt_scr_deliv]" class="lw-aio-settings-custom-switch-checkbox" id="opt_scr_deliv" <?php echo ($lw_cf7_fields_opt_scr_deliv === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="opt_scr_deliv">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Optimize Contact Form 7 scripts/styles delivery?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                  <tr><td colspan="2"><hr></td></tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[lw_cf7_fields][save_cf7_subm]" class="lw-aio-settings-custom-switch-checkbox" id="save_cf7_subm" <?php echo ($lw_cf7_fields_save_cf7_subm === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="save_cf7_subm">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Save Contact Form 7 submissions locally on the database?', 'lw_all_in_one');?></div>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title">
                      <label for="tipo_contratto"><?php esc_attr_e('Packet Type', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field">
                      <select id="tipo_contratto" name="<?php echo $this->plugin_name;?>[lw_cf7_fields][tipo_contratto]" <?php if (isset($_GET['fix_packet']) && $lw_cf7_fields_tipo_contratto == '') echo 'class="focus"' ;?>>
                        <option></option>
                        <option value="start" <?php selected( $lw_cf7_fields_tipo_contratto, 'start', TRUE ); ?>><?php _e('Go Start', 'lw_all_in_one');?></option>
                        <option value="start_standard" <?php selected( $lw_cf7_fields_tipo_contratto, 'start_standard', TRUE ); ?>><?php _e('Start Standard', 'lw_all_in_one');?></option>
                        <option value="web" <?php selected( $lw_cf7_fields_tipo_contratto, 'web', TRUE ); ?>><?php _e('Go Web', 'lw_all_in_one');?></option>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td class="lw-aio-settings-title">
                      <label for="id_contratto"><?php esc_attr_e('Packet Id', 'lw_all_in_one');?></label>
                    </td>
                    <td class="lw-aio-settings-field">
                      <input type="number" id="id_contratto" name="<?php echo $this->plugin_name; ?>[lw_cf7_fields][id_contratto]" min="1" max="100000" value="<?php echo ($lw_cf7_fields_id_contratto != '') ? $lw_cf7_fields_id_contratto : '';?>"<?php if (isset($_GET['fix_packet']) && $lw_cf7_fields_id_contratto == '') echo 'class="focus"' ;?>>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div id="tab_hf" class="tab-content<?php echo $active_tab != 'tab_hf' ? ' d-none' : ''; ?>">
              <table class="lw-aio-settings-options">
                <tbody>
                  <tr>
                    <td>
                    <p><?php _e('<b>*</b><code>HTML, JS, CSS</code> code is allowed. <b>Be careful</b> what you insert here because it may break the website!', 'lw_all_in_one');?></p>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <h3 class="shfs-labels" for="insert_header"><?php esc_attr_e( 'Scripts in header:', 'lw_all_in_one'); ?></h3>
                      <textarea rows="10" cols="100" id="insert_header" name="<?php echo $this->plugin_name; ?>[lw_hf_fields][insert_header]"><?php echo ($lw_hf_fields_insert_header !== '') ? esc_textarea(($this->lw_all_in_one_is_base64($lw_hf_fields_insert_header)) ? (base64_decode($lw_hf_fields_insert_header)) : $lw_hf_fields_insert_header) : ''; ?></textarea>
                      <p> <?php _e('Above script will be inserted into the <code>&lt;head&gt;</code> section.', 'lw_all_in_one');?></p>
                    </td>
                    <td>
                      <h3 class="shfs-labels" for="insert_footer"><?php esc_attr_e( 'Scripts in footer:', 'lw_all_in_one'); ?></h3>
                      <textarea rows="10" cols="100" id="insert_footer" name="<?php echo $this->plugin_name; ?>[lw_hf_fields][insert_footer]"><?php echo ($lw_hf_fields_insert_footer !== '') ? esc_textarea(($this->lw_all_in_one_is_base64($lw_hf_fields_insert_footer)) ? (base64_decode($lw_hf_fields_insert_footer)) : $lw_hf_fields_insert_footer) : ''; ?></textarea>
                      <p> <?php _e('Above script will be inserted just before <code>&lt;/body&gt;</code> tag using <code>wp_footer</code> hook.', 'lw_all_in_one');?></p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div id="tab_aio_options" class="tab-content<?php echo $active_tab != 'tab_aio_options' ? ' d-none' : ''; ?>">
              <table class="lw-aio-settings-options">
                <tbody>
                  <tr>
                    <td colspan="2"><h2><?php esc_attr_e('General Options', 'lw_all_in_one');?></h2></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary reset-button-div">
                        <a class="button-secondary reset-button" id="lw_aio_reset_data" href="javascript:void(0)" title="<?php esc_attr_e( 'Reset Plugin Options', 'lw_all_in_one' ); ?>"><?php esc_attr_e( 'Reset', 'lw_all_in_one' ); ?></a>
                      </div>
                      <div class="switch-desc"> <?php _e('<b>Reset Plugin Options!</b> ', 'lw_all_in_one');?> <span class="description"> <?php esc_attr_e('Revert to default plugin options.', 'lw_all_in_one');?></span></div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[lw_aio_fields][delete_data]" class="lw-aio-settings-custom-switch-checkbox" id="delete_data" <?php echo ($lw_aio_delete_data === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="delete_data">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php _e('<b>Delete saved data on plugin uninstall?</b> ', 'lw_all_in_one');?> <span class="description"> <?php esc_attr_e('If selected, saved Google Analytics events, saved Contact Form 7 submissions and Plugin options will be permanently deleted! ', 'lw_all_in_one');?></span></div>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>[lw_aio_fields][data_retention]" class="lw-aio-settings-custom-switch-checkbox" id="data_retention" <?php echo ($lw_aio_data_retention === 'on') ? 'checked="checked"' : '';?>>
                        <label class="lw-aio-settings-custom-switch-label" for="data_retention">
                          <div class="lw-aio-settings-custom-switch-inner"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php _e('<b>Enable a daily cron job to delete saved data on the database older than 14 days?</b> ', 'lw_all_in_one');?> <span class="description"> <?php esc_attr_e('Saved Contact Form 7 submissions older than 14 days will be permanently deleted!', 'lw_all_in_one');?></span></div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- sidebar -->
        <!-- <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <div class="inside">
                            <p>Everything you see here</p>
                        </div>
                    </div>
                </div>
            </div> -->
      </div>
      <br class="clear">
      <hr>
    </div>

    <?php submit_button(__('Save Options', 'lw_all_in_one'), 'primary', 'submit', TRUE);?>
  </form>
  <?php
    // echo "<pre>";
    // print_r($options);
    // echo "</pre><br>";
  ?>
</div>