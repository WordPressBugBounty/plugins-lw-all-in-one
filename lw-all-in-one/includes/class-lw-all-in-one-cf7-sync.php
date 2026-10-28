<?php
/**
 * Durable CF7 delivery queue. All attempt timestamps are UTC.
 *
 * @package Lw_All_In_One
 */

// Queue state must be read fresh; identifiers use only the trusted WP prefix and plugin constant.
// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

/** Manage schema, bounded retries and shared endpoint cooldown. */
class Lw_All_In_One_Cf7_Sync {
	const SCHEMA_VERSION = '1';
	const MAX_ATTEMPTS   = 8;
	const LOCK_OPTION    = 'lw_all_in_one_cf7_sync_lock';

	/**
	 * Upgrade before queue access, including self-updates which do not activate.
	 *
	 * @return bool Whether the required retry columns exist.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'lw_all_in_one_cf7_schema' ) === self::SCHEMA_VERSION ) {
			return true;
		}
		global $wpdb;
		$table   = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
		$collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			"CREATE TABLE $table (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      subject text NULL,
      message text NULL,
      name varchar(150) DEFAULT '' NULL,
      surname varchar(150) DEFAULT '' NULL,
      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      email varchar(100) DEFAULT '' NULL,
      phone varchar(100) DEFAULT '' NULL,
      tipo_Contratto varchar(10) DEFAULT '' NULL,
      id_Contratto varchar(10) DEFAULT '' NULL,
      submited_page text NULL,
      sent varchar(2) DEFAULT '' NULL,
      attempts int(11) unsigned NOT NULL DEFAULT 0,
      last_attempt datetime DEFAULT NULL,
      last_error varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY  (id),
      KEY delivery_queue (sent,attempts,id)
    ) $collate;"
		);
		$columns = $wpdb->get_col( "SHOW COLUMNS FROM $table" );
		if ( array_diff( array( 'attempts', 'last_attempt', 'last_error' ), $columns ) ) {
			return false; // Never send without durable attempt accounting.
		}
		// Hold the old backlog until API acceptance has been confirmed by an administrator.
		add_option( 'lw_all_in_one_cf7_legacy_cutoff', gmdate( 'Y-m-d H:i:s', time() - 14 * DAY_IN_SECONDS ), '', false );
		update_option( 'lw_all_in_one_cf7_schema', self::SCHEMA_VERSION, false );
		return true;
	}

	/**
	 * Check the shared endpoint cooldown without network I/O.
	 *
	 * @return bool Whether a probe must wait.
	 */
	public static function cooling_down() {
		$state = get_option( 'lw_all_in_one_cf7_circuit', array() );
		return ! empty( $state['retry_after'] ) && (int) $state['retry_after'] > time();
	}

	/**
	 * Open or extend the circuit, logging once until a confirmed success.
	 *
	 * @param string $reason Safe diagnostic without personal data.
	 */
	private static function endpoint_failed( $reason ) {
		$state    = get_option( 'lw_all_in_one_cf7_circuit', array() );
		$failures = min( 10, (int) ( $state['failures'] ?? 0 ) + 1 );
		$now      = time();
		update_option(
			'lw_all_in_one_cf7_circuit',
			array(
				'failures'     => $failures,
				'last_failure' => $now,
				'retry_after'  => $now + min( HOUR_IN_SECONDS, 300 * pow( 2, $failures - 1 ) ),
				'last_error'   => $reason,
			),
			false
		);
		// One log entry per outage, not per probe; never log submissions or response bodies.
		if ( empty( $state['failures'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Required once-per-outage operational diagnostic.
			error_log( 'LocalWeb CF7 sync paused: ' . $reason );
		}
	}

	/**
	 * Serialize cron and foreground delivery with an expiring atomic option.
	 *
	 * @return int|false Expiry token if this worker acquired the lock.
	 */
	private static function acquire_lock() {
		global $wpdb;
		$expires = (int) get_option( self::LOCK_OPTION, 0 );
		if ( $expires && $expires < time() ) {
			// Compare-and-delete prevents two stale-lock reclaimers deleting a new lock.
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", self::LOCK_OPTION, (string) $expires ) );
			wp_cache_delete( self::LOCK_OPTION, 'options' );
		}
		$token = time() + 60;
		// add_option() can update a racing insert; INSERT IGNORE must only acquire once.
		$inserted = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, %s)", self::LOCK_OPTION, (string) $token, 'no' ) );
		wp_cache_delete( self::LOCK_OPTION, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		return 1 === $inserted ? $token : false;
	}

	/**
	 * Identify old backlog that needs administrator approval for delivery.
	 *
	 * @param object $row Saved submission.
	 * @return bool Whether this row is held.
	 */
	public static function held( $row ) {
		$options = get_option( 'lw_all_in_one', array() );
		$cutoff  = get_option( 'lw_all_in_one_cf7_legacy_cutoff', '' );
		return 'Si' !== $row->sent && $cutoff && $row->time < $cutoff && empty( $options['lw_cf7_fields']['release_old_submissions'] );
	}

	/**
	 * Drain at most ten rows / twenty seconds, or deliver one saved submission.
	 *
	 * @param int        $id      Saved submission ID; zero drains the queue.
	 * @param array|null $payload Original submit payload, or null to build a retry payload.
	 */
	public static function run( $id = 0, $payload = null ) {
		if ( self::cooling_down() || ! self::maybe_upgrade() ) {
			return;
		}
		$lock = self::acquire_lock();
		if ( false === $lock ) {
			return;
		}
		global $wpdb;
		$table   = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
		$started = microtime( true );
		try {
			// Another worker may have opened the circuit while this worker was acquiring.
			wp_cache_delete( 'lw_all_in_one_cf7_circuit', 'options' );
			wp_cache_delete( 'notoptions', 'options' );
			if ( self::cooling_down() ) {
				return;
			}
			for ( $i = 0; $i < 10; $i++ ) {
				if ( microtime( true ) - $started >= 20 ) {
					break;
				}
				$options = get_option( 'lw_all_in_one', array() );
				$cutoff  = empty( $options['lw_cf7_fields']['release_old_submissions'] ) ? get_option( 'lw_all_in_one_cf7_legacy_cutoff', '' ) : '';
				$row     = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM $table WHERE (sent IS NULL OR sent != %s) AND attempts < %d
           AND (last_attempt IS NULL OR DATE_ADD(last_attempt, INTERVAL LEAST(86400, 300 * POW(2, LEAST(GREATEST(attempts - 1, 0), 9))) SECOND) <= UTC_TIMESTAMP())
           AND (%d = 0 OR id = %d) AND time >= %s ORDER BY id ASC LIMIT 1",
						'Si',
						self::MAX_ATTEMPTS,
						$id,
						$id,
						$cutoff ? $cutoff : '0000-00-00 00:00:00'
					)
				);
				if ( ! $row ) {
					break;
				}
				if ( ! self::deliver( $row, $payload ) || $id ) {
					break;
				}
			}
		} finally {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", self::LOCK_OPTION, (string) $lock ) );
			wp_cache_delete( self::LOCK_OPTION, 'options' );
			wp_cache_delete( 'notoptions', 'options' );
		}
	}

	/**
	 * Persist an attempt before I/O, then require an HTTP and API confirmation.
	 *
	 * @param object     $row     Saved submission with retry state.
	 * @param array|null $payload Original submit payload, or null for the retry payload.
	 * @return bool Whether the worker can continue with another row.
	 */
	private static function deliver( $row, $payload ) {
		global $wpdb;
		$table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
		// Account for the attempt BEFORE network I/O, including interrupted workers.
		$saved = $wpdb->update(
			$table,
			array(
				'attempts'     => (int) $row->attempts + 1,
				'last_attempt' => current_time( 'mysql', true ),
				'last_error'   => 'Delivery interrupted before confirmation.',
			),
			array( 'id' => $row->id )
		);
		if ( false === $saved ) {
			return false;
		}
		if ( null === $payload ) {
			$payload = array(
				'nome'           => sanitize_text_field( (string) $row->name ),
				'cognome'        => sanitize_text_field( (string) $row->surname ),
				'email'          => sanitize_email( (string) $row->email ),
				'telefono'       => sanitize_text_field( (string) $row->phone ),
				'soggetto'       => sanitize_text_field( (string) $row->subject ),
				'messaggio'      => sanitize_textarea_field( (string) $row->message ),
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Existing database/API field name.
				'tipo_Contratto' => sanitize_text_field( (string) $row->tipo_Contratto ),
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Existing database/API field name.
				'id_Contratto'   => sanitize_text_field( (string) $row->id_Contratto ),
				'submited_page'  => esc_url_raw( (string) $row->submited_page ),
			);
		}
		$send             = wp_remote_post(
			'https://localwebapi.ids.al/contactFormWeb',
			array(
				'body'        => wp_json_encode( $payload ),
				'timeout'     => 5,
				'redirection' => 0,
				'httpversion' => '1.0',
				'blocking'    => true,
			)
		);
		$endpoint_failure = false;
		if ( is_wp_error( $send ) ) {
			$reason           = 'Transport error: ' . sanitize_key( $send->get_error_code() );
			$endpoint_failure = true;
		} else {
			$status = (int) wp_remote_retrieve_response_code( $send );
			$data   = json_decode( wp_remote_retrieve_body( $send ) );
			if ( $status < 200 || $status >= 300 ) {
				$reason           = 'HTTP ' . $status;
				$endpoint_failure = 0 === $status || $status >= 500 || in_array( $status, array( 408, 429 ), true );
			} elseif ( ! is_object( $data ) || ! isset( $data->response ) || ! is_string( $data->response ) ) {
				$reason           = 'Invalid API response.';
				$endpoint_failure = true;
			} elseif ( 'OK' !== $data->response ) {
				$reason = 'API did not confirm delivery.';
			} else {
				delete_option( 'lw_all_in_one_cf7_circuit' );
				return $wpdb->update(
					$table,
					array(
						'sent'       => 'Si',
						'last_error' => '',
					),
					array( 'id' => $row->id )
				) !== false;
			}
		}
		$wpdb->update( $table, array( 'last_error' => $reason ), array( 'id' => $row->id ) );
		if ( $endpoint_failure ) {
			self::endpoint_failed( $reason );
		}
		return ! $endpoint_failure;
	}
}
