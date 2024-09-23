<?php

/**
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/public/partials
 */; ?>

<div id="lwaio-consent-bar" class="lwaio lwaio-banner lwaio-default">
  <div class="lwaio_messagebar_content">
    <h3 class="lwaio_messagebar_head"><?php echo $this->lwaiobar_settings['heading_message']; ?></h3>
    <button id="cookie_action_close_banner" class="lwaio_action_button btn" tabindex="0" aria-label="Chiudi" data-lwaio_action="close_banner"></button>
    <div class="group-description" tabindex="0">
      <p class="lwaio"><?php echo $this->lwaiobar_settings['gdpr_message']; ?>
        <a id="cookie_action_link" href="/<?php echo $this->lwaiobar_settings['ck_page_slug']; ?>" target="_blank"><?php echo $this->lwaiobar_settings['button_readmore_text']; ?></a>
      </p>
    </div>
    <div class="lwaio group-description-buttons">
      <button id="cookie_action_accept" class="lwaio_action_button btn" tabindex="0" data-lwaio_action="accept"><?php echo $this->lwaiobar_settings['button_accept_text']; ?></button>

      <button id="cookie_action_reject" class="lwaio_action_button btn" tabindex="0" data-lwaio_action="reject"><?php echo $this->lwaiobar_settings['button_decline_text']; ?></button>

      <button id="cookie_action_settings" class="lwaio_action_button btn" tabindex="0" data-lwaio_action="show_settings"><?php echo $this->lwaiobar_settings['button_settings_text']; ?></button>

      <button id="cookie_action_accept_all" class="lwaio_action_button btn" tabindex="0" data-lwaio_action="accept_all"><?php echo $this->lwaiobar_settings['button_accept_text_all']; ?></button>

    </div>
    <div class="lwaio group-switch-buttons">
      <?php foreach ($categories as $category) : ?>
        <div class="lwaio_buttons_wrapper">
          <div class="button-secondary lwaio-custom-switch">
            <?php if ($category['lwaio_category_slug'] == 'necessary') : ?>
              <input type="checkbox" id="lwaio_button_<?php echo $category['lwaio_category_slug']; ?>" class="lwaio-custom-switch-checkbox custom-switch-disabled" disabled="disabled" checked="checked" value="<?php echo $category['lwaio_category_slug']; ?>">
            <?php else : ?>
              <input type="checkbox" id="lwaio_button_<?php echo $category['lwaio_category_slug']; ?>" class="lwaio-custom-switch-checkbox" tabindex="0" <?php if ($category['is_ticked'] && !$viewed_cookie) : ?> checked="checked" <?php elseif ($category['is_ticked']) : ?> checked="checked" <?php endif; ?> value="<?php echo $category['lwaio_category_slug']; ?>">
            <?php endif; ?>
            <label class="lwaio-custom-switch-label" for="lwaio_button_<?php echo $category['lwaio_category_slug']; ?>">
              <div class="lwaio-custom-switch-inner <?php echo ($category['lwaio_category_slug'] == 'necessary') ? 'disabled' : ''; ?>"></div>
              <div class="lwaio-custom-switch-switch"></div>
            </label>
          </div>
          <label class="switch-desc" for="lwaio_button_<?php echo $category['lwaio_category_slug']; ?>"><?php echo $category['lwaio_category_name']; ?></label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="lwaio_messagebar_detail" style="display:none;max-width:1000px;">
    <div id="lwaio_messagebar_detail_body">
      <div id="lwaio_consent_tabs">
        <a id="lwaio_consent_tabs_overview" class="lwaio_consent_tab lwaio_consent_tab_item_selected" tabindex="0" href="javascript:void(0)"><?php echo $this->lwaiobar_settings['tab_1_label']; ?></a>
        <a id="lwaio_consent_tabs_about" class="lwaio_consent_tab" tabindex="0" href="javascript:void(0)"><?php echo $this->lwaiobar_settings['tab_2_label']; ?></a>
      </div>
      <div id="lwaio_consent">
        <div id="lwaio_consent_overview" style="display:block;">
          <div id="lwaio_consent_overview_cookie_container">
            <div id="lwaio_consent_overview_cookie_container_types">
              <?php foreach ($categories as $category) : ?>
                <a data-target="lwaio_consent_cookie_tabs_<?php echo $category['lwaio_category_slug']; ?>" id="lwaio_consent_overview_cookie_container_<?php echo $category['lwaio_category_slug']; ?>" class="lwaio_consent_overview_cookie_container_types <?php echo $category['lwaio_category_slug'] == 'necessary' ? 'lwaio_consent_overview_cookie_container_type_selected' : ''; ?>" tabindex="0" href="javascript:void(0)"><?php echo $category['lwaio_category_name']; ?> (<?php echo $category['total']; ?>)</a>
              <?php endforeach; ?>
            </div>
            <div id="lwaio_consent_overview_cookie_container_type_details">
              <?php foreach ($categories as $category) : ?>
                <div id="lwaio_consent_cookie_tabs_<?php echo $category['lwaio_category_slug']; ?>" tabindex="0" <?php echo $category['lwaio_category_slug'] == 'necessary' ? 'style="display:block;"' : 'style="display:none;"'; ?> class="lwaio_consent_cookie_type_details">
                  <div class="lwaio_consent_cookie_type_intro">
                    <?php echo $category['lwaio_category_description']; ?>
                  </div>
                  <div class="lwaio_consent_cookie_type_table_container">
                    <?php if ($category['total'] > 0) : ?>
                      <table id="lwaio_messagebar_detail_table_<?php echo $category['lwaio_category_slug']; ?>" class="lwaio_consent_cookie_type_table">
                        <thead>
                          <tr>
                            <th scope="col"><?php echo __('Name', 'lw_all_in_one'); ?></th>
                            <th scope="col"><?php echo __('Domain', 'lw_all_in_one'); ?></th>
                            <th scope="col"><?php echo __('Purpose', 'lw_all_in_one'); ?></th>
                            <th scope="col"><?php echo __('Expiry', 'lw_all_in_one'); ?></th>
                            <th scope="col"><?php echo __('Type', 'lw_all_in_one'); ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($category['data'] as $cookie) : ?>
                            <tr>
                              <td title="<?php echo $cookie['name']; ?>"><?php echo $cookie['name']; ?></td>
                              <td title="<?php echo $cookie['domain']; ?>"><?php echo $cookie['domain']; ?></td>
                              <td title="<?php echo $cookie['description']; ?>"><?php echo $cookie['description']; ?></td>
                              <td title="<?php echo $cookie['duration']; ?>"><?php echo $cookie['duration']; ?></td>
                              <td title="<?php echo $cookie['type']; ?>"><?php echo $cookie['type']; ?></td>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    <?php else : ?>
                      <?php echo $this->lwaiobar_settings['no_cookies_in_cat']; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div id="lwaio_consent_about" style="display:none;">
          <?php echo $this->lwaiobar_settings['about_ck_message']; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="lwaio-consent-show-again">
  <span><?php echo $this->lwaiobar_settings['show_again_text']; ?></span>
</div>