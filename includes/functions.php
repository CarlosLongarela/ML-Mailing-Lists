<?php
/**
 * Helper functions for ML Mailing Lists
 *
 * @package ML_Mailing_Lists
 * @subpackage Functions
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if a subscription exists for an email in a specific list
 *
 * @param string $email   The email address to check.
 * @param int    $list_id The mailing list ID.
 * @return bool True if subscription exists, false otherwise.
 */
function ml_subscription_exists( $email, $list_id ) {
	if ( ! is_email( $email ) || empty( $list_id ) ) {
		return false;
	}

	$existing_subscription = get_posts(
		array(
			'post_type'      => 'ml_suscriptor',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => 'correo',
					'value'   => $email,
					'compare' => '=',
				),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'ml_lista',
					'field'    => 'term_id',
					'terms'    => $list_id,
				),
			),
		)
	);

	return ! empty( $existing_subscription );
}

/**
 * Get subscriber data by email
 *
 * @param string $email The email address.
 * @return array|false Subscriber data array or false if not found.
 */
function ml_get_subscriber_by_email( $email ) {
	if ( ! is_email( $email ) ) {
		return false;
	}

	$subscribers = get_posts(
		array(
			'post_type'      => 'ml_suscriptor',
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'     => 'correo',
					'value'   => $email,
					'compare' => '=',
				),
			),
		)
	);

	if ( empty( $subscribers ) ) {
		return false;
	}

	$subscriber = $subscribers[0];

	return array(
		'id'                => $subscriber->ID,
		'nome'              => get_post_meta( $subscriber->ID, 'nome', true ),
		'apelido'           => get_post_meta( $subscriber->ID, 'apelido', true ),
		'correo'            => get_post_meta( $subscriber->ID, 'correo', true ),
		'data_subscripcion' => get_post_meta( $subscriber->ID, 'data_subscripcion', true ),
		'lists'             => wp_get_post_terms( $subscriber->ID, 'ml_lista', array( 'fields' => 'names' ) ),
	);
}

/**
 * Get all subscribers from a specific mailing list
 *
 * @param int $list_id The mailing list ID.
 * @return array Array of subscriber data.
 */
function ml_get_list_subscribers( $list_id ) {
	if ( empty( $list_id ) ) {
		return array();
	}

	$subscribers = get_posts(
		array(
			'post_type'      => 'ml_suscriptor',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'ml_lista',
					'field'    => 'term_id',
					'terms'    => $list_id,
				),
			),
		)
	);

	$subscriber_data = array();

	foreach ( $subscribers as $subscriber ) {
		$subscriber_data[] = array(
			'id'                => $subscriber->ID,
			'nome'              => get_post_meta( $subscriber->ID, 'nome', true ),
			'apelido'           => get_post_meta( $subscriber->ID, 'apelido', true ),
			'correo'            => get_post_meta( $subscriber->ID, 'correo', true ),
			'data_subscripcion' => get_post_meta( $subscriber->ID, 'data_subscripcion', true ),
		);
	}

	return $subscriber_data;
}

/**
 * Get mailing list statistics
 *
 * @param int $list_id Optional. Specific list ID.
 * @return array Statistics array.
 */
function ml_get_list_stats( $list_id = 0 ) {
	if ( $list_id > 0 ) {
		$term = get_term( $list_id, 'ml_lista' );

		if ( is_wp_error( $term ) || ! $term ) {
			return array();
		}

		return array(
			'list_name'        => $term->name,
			'list_id'          => $term->term_id,
			'subscriber_count' => $term->count,
			'description'      => $term->description,
		);
	}

	// Get all lists stats.
	$lists = get_terms(
		array(
			'taxonomy'   => 'ml_lista',
			'hide_empty' => false,
		)
	);

	$total_subscribers = wp_count_posts( 'ml_suscriptor' )->publish;

	$stats = array(
		'total_lists'       => count( $lists ),
		'total_subscribers' => $total_subscribers,
		'lists'             => array(),
	);

	foreach ( $lists as $list ) {
		$stats['lists'][] = array(
			'name'             => $list->name,
			'id'               => $list->term_id,
			'subscriber_count' => $list->count,
			'description'      => $list->description,
		);
	}

	return $stats;
}

/**
 * Validate email address format
 *
 * @param string $email Email address to validate.
 * @return bool True if valid, false otherwise.
 */
function ml_is_valid_email( $email ) {
	return is_email( $email );
}

/**
 * Sanitize subscriber data
 *
 * @param array $data Raw subscriber data.
 * @return array Sanitized data.
 */
function ml_sanitize_subscriber_data( $data ) {
	return array(
		'nome'    => isset( $data['nome'] ) ? sanitize_text_field( $data['nome'] ) : '',
		'apelido' => isset( $data['apelido'] ) ? sanitize_text_field( $data['apelido'] ) : '',
		'correo'  => isset( $data['correo'] ) ? sanitize_email( $data['correo'] ) : '',
	);
}

/**
 * Log plugin activity
 *
 * @param string $action    Action performed.
 * @param array  $details   Action details.
 * @param int    $user_id   Optional. User ID.
 */
function ml_log_activity( $action, $details = array(), $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$log_entry = array(
		'timestamp' => current_time( 'mysql' ),
		'action'    => sanitize_text_field( $action ),
		'details'   => $details,
		'user_id'   => $user_id,
		'ip'        => ML_Security::get_user_ip(),
	);

	$logs   = get_option( 'ml_activity_logs', array() );
	$logs[] = $log_entry;

	// Keep only the last 500 log entries.
	if ( count( $logs ) > 500 ) {
		$logs = array_slice( $logs, -500 );
	}

	update_option( 'ml_activity_logs', $logs );
}

/**
 * Get recent activity logs
 *
 * @param int $limit Number of logs to retrieve.
 * @return array Recent activity logs.
 */
function ml_get_recent_activity( $limit = 50 ) {
	$logs = get_option( 'ml_activity_logs', array() );

	// Return most recent logs.
	return array_slice( array_reverse( $logs ), 0, $limit );
}

/**
 * Format date for display
 *
 * @param string $date      Date string.
 * @param string $format    Optional. Date format.
 * @return string Formatted date.
 */
function ml_format_date( $date, $format = 'd/m/Y H:i' ) {
	if ( empty( $date ) ) {
		return '';
	}

	$timestamp = is_numeric( $date ) ? $date : strtotime( $date );

	if ( false === $timestamp ) {
		return $date;
	}

	return date_i18n( $format, $timestamp );
}

/**
 * Check if user has permission to manage mailing lists
 *
 * @param int $user_id Optional. User ID to check.
 * @return bool True if user has permission, false otherwise.
 */
function ml_user_can_manage_lists( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$user = get_user_by( 'id', $user_id );

	if ( ! $user ) {
		return false;
	}

	return user_can( $user, 'manage_options' ) || user_can( $user, 'edit_posts' );
}

/**
 * Get plugin version
 *
 * @return string Plugin version.
 */
function ml_get_plugin_version() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin_data = get_plugin_data( ML_PLUGIN_FILE );

	return $plugin_data['Version'];
}

/**
 * Debug function for development
 *
 * @param mixed  $data  Data to debug.
 * @param string $label Optional. Debug label.
 */
function ml_debug( $data, $label = '' ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	$output  = ! empty( $label ) ? "[ML Debug - {$label}] " : '[ML Debug] ';
	$output .= print_r( $data, true );

	error_log( $output );
}
