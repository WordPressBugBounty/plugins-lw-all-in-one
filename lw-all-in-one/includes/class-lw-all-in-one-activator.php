<?php

/**
 * Fired during plugin activation
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 */

/**
 * Fired during plugin activation.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Activator {

  public static function activate() {
    if (!get_option('lw_all_in_one_version')) {
      add_option('lw_all_in_one_version', LW_ALL_IN_ONE_VERSION);
    }
    // Check if Web Instant Messenger options exist
    $verification_status = $token = $wim_activate = $rag_soc = $auto_show_wim = $show_wim_after = $show_mobile = $lingua = $messaggio_0 = $messaggio_1 = $cf7_activate = $ck_activate = $save_cf7_subm = $opt_scr_deliv = $ga_activate = $tracking_id = $save_ga_events = $monitor_email_link = $monitor_tel_link = $monitor_form_submit = $monitor_woocommerce_data = '';
    if ($wim_activation_status = get_option('wim_activation_status')) {
      $verification_status = ($wim_activation_status['activation_status'] == 1) ? 'verified' : '';
      $token = $wim_activation_status['token'];
    }
    if ($wim_old_options = get_option('web-instant-messenger')) {
      $wim_activate = ($wim_old_options['activate'] == 1) ? 'on' : '';
      $rag_soc = $wim_old_options['rag_soc'];
      $auto_show_wim = $wim_old_options['auto_show_wim'];
      $show_wim_after = $wim_old_options['show_wim_after'];
      $show_mobile = $wim_old_options['show_mobile'];
      $lingua = $wim_old_options['lingua'];
      $messaggio_0 = $wim_old_options['messaggio_0'];
      $messaggio_1 = $wim_old_options['messaggio_1'];
    }
    // Check if LW Contact Form 7 Addon plugin is activated
    if (is_plugin_active('lw-contact-form/localweb.php')) {
      $cf7_activate = $save_cf7_subm = $opt_scr_deliv = 'on';
    }
    //
    if (get_option('gadwp_options')) {
      $gadwp_options = (array) json_decode( get_option( 'gadwp_options' ) );
      $locked_profile = $gadwp_options['tableid_jail'];
      $profiles = $gadwp_options['ga_profiles_list'];
      if (!empty($profiles) ) {
				foreach ( $profiles as $item ) {
					if ( $item[1] == $locked_profile ) {
						$tracking_id = $item[2];
					}
				}
			}
    }
    if (lw_all_in_one_validate_tracking_id($tracking_id)) {
      $ga_activate = $save_ga_events = $monitor_email_link = $monitor_tel_link = $monitor_form_submit = 'on';
      if (is_plugin_active('woocommerce/woocommerce.php')) {
        $monitor_woocommerce_data = 'on';
      }
    }

    if (get_locale() == 'es_ES') {
      $ck_fields['ck_page_slug'] = 'las-cookies-que-utilizamos';
      $ck_fields['heading_message'] = 'Este sitio web utiliza cookies';
      $ck_fields['gdpr_message'] = 'Utilizamos cookies para personalizar contenido y anuncios, para proporcionar funciones de redes sociales y para analizar nuestro tráfico. También compartimos información sobre su uso de nuestro sitio con nuestros socios de redes sociales, publicidad y análisis, que pueden combinarla con otra información que les haya proporcionado o que hayan recopilado a partir del uso de sus servicios.';
      $ck_fields['about_ck_message'] = 'Las cookies son pequeños archivos de texto que pueden ser utilizados por los sitios web para hacer que la experiencia del usuario sea más eficiente. La ley establece que podemos almacenar cookies en su dispositivo si son estrictamente necesarias para el funcionamiento de este sitio. Para todos los demás tipos de cookies necesitamos su permiso. Este sitio utiliza diferentes tipos de cookies. Algunas cookies son colocadas por servicios de terceros que aparecen en nuestras páginas. En cualquier momento puede cambiar o retirar su consentimiento de la Declaración de cookies en nuestro sitio web. Obtenga más información sobre quiénes somos, cómo puede contactarnos y cómo tratamos los datos personales en nuestra Política de privacidad. Especifique su ID de consentimiento y la fecha en que nos contactó con respecto a su consentimiento.';
    } elseif (get_locale() == 'it_IT') {
      $ck_fields['ck_page_slug'] = 'cookie-policy';
      $ck_fields['heading_message'] = 'Questo sito web utilizza i cookie!';
      $ck_fields['gdpr_message'] = 'Utilizziamo i cookie per personalizzare contenuti ed annunci, per fornire funzionalità dei social media e per analizzare il nostro traffico. Condividiamo inoltre informazioni sul modo in cui utilizza il nostro sito con i nostri partner che si occupano di analisi dei dati web, pubblicità e social media, i quali potrebbero combinarle con altre informazioni che ha fornito loro o che hanno raccolto dal suo utilizzo dei loro servizi.';
      $ck_fields['about_ck_message'] = "I cookie sono piccoli file di testo che possono essere utilizzati dai siti web per rendere più efficiente l’esperienza per l’utente. La legge afferma che possiamo memorizzare i cookie sul suo dispositivo se sono strettamente necessari per il funzionamento di questo sito. Per tutti gli altri tipi di cookie abbiamo bisogno del suo permesso. Questo sito utilizza diversi tipi di cookie. Alcuni cookie sono collocati da servizi di terzi che compaiono sulle nostre pagine. In qualsiasi momento è possibile modificare o revocare il proprio consenso dalla Dichiarazione dei cookie sul nostro sito Web. Scopra di più su chi siamo, come può contattarci e come trattiamo i dati personali nella nostra Informativa sulla privacy. Specifica l’ID del tuo consenso e la data di quando ci hai contattati per quanto riguarda il tuo consenso.";
    } else {
      $ck_fields['ck_page_slug'] = 'cookie-policy';
      $ck_fields['heading_message'] = 'This website uses cookies!';
      $ck_fields['gdpr_message'] = 'We use cookies to personalize content and ads, to provide social media features and to analyze our traffic. We also share information about how you use our site with our analytics, advertising and social media partners, who may combine it with other information that you have provided to them or that they have collected from your use of their services.';
      $ck_fields['about_ck_message'] = "Cookies are small text files that can be used by websites to make the user experience more efficient. The law states that we can store cookies on your device if they are strictly necessary for the operation of this site. For all other types of cookies we need your permission. This site uses different types of cookies. Some cookies are placed by third-party services that appear on our pages. You can change or withdraw your consent at any time from the Cookie Declaration on our website. Find out more about who we are, how you can contact us and how we process personal data in our Privacy Policy. Specify your consent ID and the date you contacted us regarding your consent.";
    }

    if (!get_option('lw_all_in_one')) {
      $initial_attivation_options = array(
        'ga_activate' => $ga_activate,
        'ga_fields' => array(
          'tracking_id' => $tracking_id,
          'save_ga_events' => $save_ga_events,
          'monitor_email_link' => $monitor_email_link,
          'monitor_tel_link' => $monitor_tel_link,
          'monitor_form_submit' => $monitor_form_submit,
          'monitor_woocommerce_data' => $monitor_woocommerce_data,
        ),
        'wim_activate' => $wim_activate,
        'wim_fields' => array(
          'verification_status' => $verification_status,
          'token' => $token,
          'rag_soc' => $rag_soc,
          'auto_show_wim' => $auto_show_wim,
          'show_wim_after' => $show_wim_after,
          'show_mobile' => $show_mobile,
          'lingua' => $lingua,
          'messaggio_0' => $messaggio_0,
          'messaggio_1' => $messaggio_1,
        ),
        'cf7_activate' => $cf7_activate,
        'lw_cf7_fields' => array(
          'save_cf7_subm' => $save_cf7_subm,
          'opt_scr_deliv' => $opt_scr_deliv,
        ),
        'ck_activate' => $ck_activate,
        'ck_fields' => array(
          'banner_position' => 'bottom',
          'ck_page_slug' => $ck_fields['ck_page_slug'],
          'primary_color' => '#18a300',
          'secondary_color' => '#333333',
          'heading_message' => $ck_fields['heading_message'],
          'gdpr_message' => $ck_fields['gdpr_message'],
          'about_ck_message' => $ck_fields['about_ck_message'],
        ),
        'lw_hf_fields' => array(
          'insert_header' => '',
          'insert_footer' => '',
        ),
        'lw_aio_fields' => array(
          'delete_data' => '',
          'data_retention' => 'on',
        ),
      );
      add_option('lw_all_in_one', $initial_attivation_options);
    }
    // if (version_compare($lw_all_in_one_version, '1.4.0') < 0) {
    //   $exiting_options = get_option('lw_all_in_one');
    //   if ($exiting_options) {
    //     $new_options = array();
    //     $new_options['lw_hf_fields']['insert_header'] = base64_decode($exiting_options['lw_hf_fields']['insert_header']);
    //     $new_options['lw_hf_fields']['insert_footer'] = base64_decode($exiting_options['lw_hf_fields']['insert_footer']);
    //     $new_options['lw_aio_fields']['delete_data'] = '';
    //     $new_options['lw_aio_fields']['data_retention'] = 'on';
    //     $new_options_update = array_merge($exiting_options, $new_options);
    //     update_option( 'lw_all_in_one', $new_options_update );
    //   }
    // }
    // update_option('lw_all_in_one_version', LW_ALL_IN_ONE_VERSION);
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $a_events_table = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
    $cf7_table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    if ($wpdb->get_var("show tables like '$a_events_table'") != $a_events_table) {
      $sql1 = "CREATE TABLE $a_events_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            ga_category varchar(250) DEFAULT '' NULL,
            ga_action varchar(250) DEFAULT '' NULL,
            ga_label varchar(250) DEFAULT '' NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
      dbDelta($sql1);
    }

    if ($wpdb->get_var("show tables like '$cf7_table'") != $cf7_table) {
      $sql2 = "CREATE TABLE $cf7_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            subject text DEFAULT '' NULL,
            message text DEFAULT '' NULL,
            name varchar(150) DEFAULT '' NULL,
            surname varchar(150) DEFAULT '' NULL,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            email varchar(100) DEFAULT '' NULL,
            phone varchar(100) DEFAULT '' NULL,
            tipo_Contratto varchar(10) DEFAULT '' NULL,
            id_Contratto varchar(10) DEFAULT '' NULL,
            submited_page text DEFAULT '' NULL,
            sent varchar(2) DEFAULT '' NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
      dbDelta($sql2);
    }

    $old_cf7_table = $wpdb->prefix . 'inserimenti_cf';
    $old_cf7_table_transfer_err = array();
    if ($wpdb->get_var("show tables like '$old_cf7_table'") == $old_cf7_table) {
      $inserimenti_cf_results = $wpdb->get_results("SELECT * FROM $old_cf7_table");
      if ($wpdb->num_rows > 0) {
        foreach ($inserimenti_cf_results as $cf) {
          $new_time = date('Y-m-d H:i:s', $cf->time);
          $old_data = array('subject' => $cf->soggetto, 'message' => $cf->messaggio, 'name' => $cf->nome, 'surname' => $cf->cognome, 'time' => $new_time, 'email' => $cf->email, 'phone' => $cf->telefono, 'tipo_Contratto' => $cf->tipo_Contratto, 'id_Contratto' => $cf->id_Contratto, 'submited_page' => $cf->submited_page, 'sent' => $cf->inviato);
          $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
          if (!$wpdb->insert($cf7_table, $old_data, $format)) {
            array_push($old_cf7_table_transfer_err, 'error');
          }
        }
      }
      if (empty($old_cf7_table_transfer_err)) {
        $wpdb->query("DROP TABLE IF EXISTS $old_cf7_table");
      }
    }

    if (is_plugin_active('web-instant-messenger/web-instant-messenger.php')) {
      deactivate_plugins('web-instant-messenger/web-instant-messenger.php');
    }
    if (is_plugin_active('lw-contact-form/localweb.php')) {
      deactivate_plugins('lw-contact-form/localweb.php');
    }
    if (is_plugin_active('lw-cookie-privacy/lw-cookie-privacy.php')) {
      deactivate_plugins('lw-cookie-privacy/lw-cookie-privacy.php');
    }
    if (is_plugin_active('google-analytics-dashboard-for-wp/gadwp.php')) {
      deactivate_plugins('google-analytics-dashboard-for-wp/gadwp.php');
    }
    if (is_plugin_active('woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php')) {
      deactivate_plugins('woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php');
    }
    // if (is_plugin_active('italy-cookie-choices/italy-cookie-choices.php')) {
    //   deactivate_plugins('italy-cookie-choices/italy-cookie-choices.php');
    // }
    if (is_plugin_active('wp-fastest-cache/wpFastestCache.php')) {
      // Exclude from cache 'lwaio_*' cookies
      update_option( 'WpFastestCacheExclude', json_encode([["prefix" => "contain", "content" => "lwaio_", "type" => "cookie"]]));
      if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')){
        $GLOBALS['wp_fastest_cache']->deleteCache(true);
      }
    }

    // Update translation files
    // If translation files do not exist under /wp-content/languages/plugins/, copy them from the plugin directory /languages/
    $translated_locales = array('es_ES', 'it_IT');
    foreach ($translated_locales as $locale) {
      if (!file_exists(WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.mo')) {
        copy(dirname(LW_ALL_IN_ONE_PLUGIN_MAIN_FILE) . '/languages/lw_all_in_one-'.$locale.'.po', WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.po');
        copy(dirname(LW_ALL_IN_ONE_PLUGIN_MAIN_FILE) . '/languages/lw_all_in_one-'.$locale.'.mo', WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.mo');
      }
    }

  }

}
