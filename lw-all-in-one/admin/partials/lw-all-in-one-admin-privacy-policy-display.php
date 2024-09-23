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

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//Plugin options
$options = get_option($this->plugin_name . '_privacy_pages');
$cookie_page_options = (isset($options['cookie-policy'])) ? $options['cookie-policy'] : array();
$privacy_page_options = (isset($options['informativa-sul-trattamento-dei-dati-personali'])) ? $options['informativa-sul-trattamento-dei-dati-personali'] : array();
$trattamento_dati_page_options = (isset($options['informativa-trattamento-dati'])) ? $options['informativa-trattamento-dati'] : array();
?>
<div class="wrap">

  <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
  <?php settings_errors();?>
  <hr>
  <div class="warning">
      <h3><?php esc_attr_e('WARNING!', 'lw_all_in_one');?></h3>
    <p>
      <?php _e('Use this section only if this website\'s domain is registered by <b>LocalWeb Srl</b>.', 'lw_all_in_one');?>
    </p>
    <p>
      <?php esc_html_e('Link at FOOTER section pages "Informativa sul trattamento dei dati personali" and "Cookie Policy".', 'lw_all_in_one');?>
    </p>
    <p>
      <?php esc_attr_e('Link "Informativa Trattamento Dati" in all contact forms of the website.', 'lw_all_in_one');?>
    </p>
  </div>
  <hr>
  <form method="post" name="<?php echo $this->plugin_name; ?>_privacy_policy_pages" id="<?php echo $this->plugin_name; ?>_privacy_policy_pages" action="">

    <div id="poststuff" class="lw-aio">
      <div id="post-body" class="metabox-holder columns-2">
        <!-- main content -->
        <div id="post-body-content">
          <div class="postbox">
            <div class="inside">
              <h2><?php esc_attr_e('Choose which pages you want to create:', 'lw_all_in_one');?></h2>
              <table class="lw-aio-settings-options">
                <tbody>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                    <?php if (is_array($cookie_page_options) && !empty($cookie_page_options)) : ?>
                    <p>
                      <?php esc_attr_e('Cookie Policy page already created: ', 'lw_all_in_one');?>
                        <input type="hidden" value="on" name="<?php echo $this->plugin_name; ?>_cookie_page" id="<?php echo $this->plugin_name; ?>_cookie_page">
                        <a href="<?php echo get_permalink(absint($cookie_page_options['post_id'])); ?>" target="_blank">
                          <?php echo get_the_title(absint($cookie_page_options['post_id'])); ?>
                        </a>
                      </p>
                    <?php else : ?>
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>_cookie_page" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_cookie_page">
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_cookie_page">
                          <div class="lw-aio-settings-custom-switch-inner privacy-page"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Create Cookie Policy page?', 'lw_all_in_one');?></div>
                    <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                    <?php if (is_array($privacy_page_options) && !empty($privacy_page_options)) : ?>
                    <p>
                      <?php esc_attr_e('Privacy Policy page already created: ', 'lw_all_in_one');?>
                      <input type="hidden" value="on" name="<?php echo $this->plugin_name; ?>_privacy_page" id="<?php echo $this->plugin_name; ?>_privacy_page">
                      <a href="<?php echo get_permalink(absint($privacy_page_options['post_id'])); ?>" target="_blank">
                        <?php echo get_the_title(absint($privacy_page_options['post_id'])); ?>
                      </a>
                    </p>
                    <?php else : ?>
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>_privacy_page" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_privacy_page">
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_privacy_page">
                          <div class="lw-aio-settings-custom-switch-inner privacy-page"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Create Privacy Policy page?', 'lw_all_in_one');?></div>
                    <?php endif; ?>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="2" class="lw-aio-settings-title">
                    <?php if (is_array($trattamento_dati_page_options) && !empty($trattamento_dati_page_options)) : ?>
                    <p>
                      <?php esc_attr_e('Information Treatment page already created: ', 'lw_all_in_one');?>
                      <input type="hidden" value="on" name="<?php echo $this->plugin_name; ?>_trattamento_dati_page" id="<?php echo $this->plugin_name; ?>_trattamento_dati_page">
                      <a href="<?php echo get_permalink(absint($trattamento_dati_page_options['post_id'])); ?>" target="_blank">
                        <?php echo get_the_title(absint($trattamento_dati_page_options['post_id'])); ?>
                      </a>
                    </p>
                    <?php else : ?>
                      <div class="button-secondary lw-aio-settings-custom-switch">
                        <input type="checkbox" name="<?php echo $this->plugin_name; ?>_trattamento_dati_page" class="lw-aio-settings-custom-switch-checkbox" id="<?php echo $this->plugin_name; ?>_trattamento_dati_page">
                        <label class="lw-aio-settings-custom-switch-label" for="<?php echo $this->plugin_name; ?>_trattamento_dati_page">
                          <div class="lw-aio-settings-custom-switch-inner privacy-page"></div>
                          <div class="lw-aio-settings-custom-switch-switch"></div>
                        </label>
                      </div>
                      <div class="switch-desc"> <?php esc_attr_e('Create Information Treatment page?', 'lw_all_in_one');?></div>
                    <?php endif; ?>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div id="created_pages_response"></div>
              <!-- <br class="clear"> -->
                <?php if(is_array($options) && count($options) == 3) : ?>
                  <?php submit_button(__('Update Pages', 'lw_all_in_one'), 'primary', 'submit_create_pages', TRUE);?>
                <?php else: ?>
                  <?php submit_button(__('Create Pages', 'lw_all_in_one'), 'primary', 'submit_create_pages', TRUE);?>
                <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>