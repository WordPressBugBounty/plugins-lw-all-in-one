<?php
/** Durable, atomic security state. No bearer tokens or raw IPs are stored. */
class Lw_All_In_One_Security {
  const SCHEMA_VERSION = '1';

  public static function register_hooks() {
    add_action('lw_all_in_one_security_cleanup', array(__CLASS__, 'cleanup'));
    if (!wp_next_scheduled('lw_all_in_one_security_cleanup')) {
      wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'lw_all_in_one_security_cleanup');
    }
  }

  public static function maybe_upgrade() {
    if (get_option('lw_all_in_one_security_schema') === self::SCHEMA_VERSION) {
      return true;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'lw_aio_security';
    $collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE $table (
      state_key varchar(64) NOT NULL,
      expires bigint(20) unsigned NOT NULL,
      hits int(10) unsigned NOT NULL DEFAULT 0,
      PRIMARY KEY  (state_key),
      KEY expires (expires)
    ) $collate;");
    if ((int) $wpdb->get_var("SELECT COUNT(*) FROM $table") < 0 || $wpdb->last_error) {
      return false;
    }
    update_option('lw_all_in_one_security_schema', self::SCHEMA_VERSION, false);
    return true;
  }

  /** Unique insertion is the replay decision; cache eviction cannot undo it. */
  public static function consume_sso($nonce, $expires) {
    if (!self::maybe_upgrade()) {
      return false;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'lw_aio_security';
    return 1 === $wpdb->query($wpdb->prepare(
      "INSERT IGNORE INTO $table (state_key, expires, hits) VALUES (%s, %d, 1)",
      hash('sha256', 'sso:' . $nonce), $expires
    ));
  }

  /** Fixed-window counter: conditional UPDATE atomically admits at most limit. */
  public static function rate_limit($identity, $limit) {
    if (!self::maybe_upgrade()) {
      return false;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'lw_aio_security';
    $window = (int) floor(time() / MINUTE_IN_SECONDS);
    $key = hash_hmac('sha256', 'rate:' . $window . ':' . $identity, wp_salt('nonce'));
    $inserted = $wpdb->query($wpdb->prepare(
      "INSERT IGNORE INTO $table (state_key, expires, hits) VALUES (%s, %d, 0)", $key, ($window + 1) * MINUTE_IN_SECONDS
    ));
    if (false === $inserted) {
      return false;
    }
    return 1 === $wpdb->query($wpdb->prepare(
      "UPDATE $table SET hits = hits + 1 WHERE state_key = %s AND hits < %d", $key, $limit
    ));
  }

  public static function cleanup() {
    if (!self::maybe_upgrade()) {
      return;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'lw_aio_security';
    $wpdb->query($wpdb->prepare("DELETE FROM $table WHERE expires < %d LIMIT 25000", time()));
    $events = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
    // Only anonymous analytics events expire here. Never prune failed enquiries.
    $options = get_option('lw_all_in_one', array());
    $days = max(1, min(3650, (int) ($options['lw_aio_fields']['ga_retention_days'] ?? 90)));
    $wpdb->query($wpdb->prepare("DELETE FROM $events WHERE time < %s LIMIT 10000", gmdate('Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS)));
  }

  /** Remove only this plugin's cache files under the canonical uploads root. */
  public static function clear_caches() {
    $uploads = wp_upload_dir(null, false);
    $root = realpath($uploads['basedir']);
    if (!$root) {
      return;
    }
    foreach (array('lw-aio-webp-cache' => '*.webp', 'lw-aio-critical-css' => '*.css') as $name => $pattern) {
      $directory = realpath($root . '/' . $name);
      if (!$directory || is_link($root . '/' . $name) || dirname($directory) !== $root) {
        continue;
      }
      foreach (glob($directory . '/' . $pattern) ?: array() as $file) {
        if (is_file($file) && !is_link($file)) {
          unlink($file);
        }
      }
      if (!(glob($directory . '/*') ?: array())) {
        rmdir($directory);
      }
    }
  }

  public static function consent($purpose) {
    $raw = $_COOKIE['lwaio_consent_preferences'] ?? '';
    if (!is_string($raw) || strlen($raw) > 2048) {
      return false;
    }
    $preferences = json_decode(wp_unslash($raw), true);
    return is_array($preferences) && ($preferences[$purpose] ?? '') === 'yes';
  }
}
