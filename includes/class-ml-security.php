<?php
/**
 * Security class for ML Mailing Lists
 *
 * @package ML_Mailing_Lists
 * @namespace ML_Mailing_Lists
 * @since 1.0.1
 */

namespace ML_Mailing_Lists;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Security
 *
 * Handles all security-related functionality.
 */
class Security {

	/**
	 * Unique instance of the class
	 *
	 * @var Security
	 */
	private static $instance = null;

	/**
	 * Private constructor for singleton pattern
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Get unique instance of the class
	 *
	 * @return Security
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize security hooks
	 */
	private function init() {
		// Security hooks are handled directly in the classes that need them.
	}

	/**
	 * Verify security nonce
	 *
	 * @param string $nonce  The nonce to verify.
	 * @param string $action The nonce action.
	 * @return bool True if valid, false otherwise.
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Create security nonce
	 *
	 * @param string $action The action for the nonce.
	 * @return string The generated nonce.
	 */
	public static function create_nonce( $action ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Check submission rate limit
	 *
	 * @param string $ip          The user's IP address.
	 * @param int    $max_attempts Maximum number of allowed attempts.
	 * @param int    $time_window  Time window in seconds.
	 * @return bool True if within limit, false if exceeds.
	 */
	public static function check_rate_limit( $ip, $max_attempts = 3, $time_window = HOUR_IN_SECONDS ) {
		$rate_limit_key = 'ml_rate_limit_' . md5( $ip );
		$current_count  = get_transient( $rate_limit_key );

		if ( $current_count && $current_count >= $max_attempts ) {
			return false;
		}

		return true;
	}

	/**
	 * Increment rate limit counter
	 *
	 * @param string $ip         The user's IP address.
	 * @param int    $time_window Time window in seconds.
	 */
	public static function increment_rate_limit( $ip, $time_window = HOUR_IN_SECONDS ) {
		$rate_limit_key = 'ml_rate_limit_' . md5( $ip );
		$current_count  = get_transient( $rate_limit_key );
		$new_count      = $current_count ? $current_count + 1 : 1;

		set_transient( $rate_limit_key, $new_count, $time_window );
	}

	/**
	 * Get user IP address safely
	 *
	 * @return string The user's IP address.
	 */
	public static function get_user_ip() {
		$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

				// Handle comma-separated IPs (proxy headers).
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}

				// Validate IP format.
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Validate subscription data
	 *
	 * @param array $data The form data.
	 * @return array Array with 'valid' boolean and 'message' string.
	 */
	public static function validate_subscription_data( $data ) {
		$errors = array();

		// Basic validation.
		if ( empty( $data['name'] ) ) {
			$errors[] = 'O nome é obrigatorio.';
		} elseif ( strlen( $data['name'] ) > 100 ) {
			$errors[] = 'O nome non pode superar os 100 caracteres.';
		}

		if ( empty( $data['surname'] ) ) {
			$errors[] = 'Os apelidos son obrigatorios.';
		} elseif ( strlen( $data['surname'] ) > 100 ) {
			$errors[] = 'Os apelidos non poden superar os 100 caracteres.';
		}

		if ( empty( $data['email'] ) ) {
			$errors[] = 'O correo é obrigatorio.';
		} elseif ( ! is_email( $data['email'] ) ) {
			$errors[] = 'Por favor, introduza un correo válido.';
		} elseif ( strlen( $data['email'] ) > 255 ) {
			$errors[] = 'O correo non pode superar os 255 caracteres.';
		}

		// Detect suspicious patterns (basic spam detection).
		$suspicious_patterns = array( 'http://', 'https://', 'www.', '.com', '.net', '.org' );
		foreach ( $suspicious_patterns as $pattern ) {
			if ( stripos( $data['name'], $pattern ) !== false || stripos( $data['surname'], $pattern ) !== false ) {
				$errors[] = 'Os datos introducidos parecen conter contido spam.';
				break;
			}
		}

		return array(
			'valid'   => empty( $errors ),
			'message' => empty( $errors ) ? '' : implode( ' ', $errors ),
		);
	}

	/**
	 * Detect honeypot
	 *
	 * @param string $honeypot_value Honeypot field value.
	 * @return bool True if spam (honeypot detected), false if legitimate.
	 */
	public static function is_honeypot_triggered( $honeypot_value ) {
		return ! empty( $honeypot_value );
	}

	/**
	 * Sanitize text input
	 *
	 * @param string $input The text to sanitize.
	 * @return string The sanitized text.
	 */
	public static function sanitize_text_input( $input ) {
		return sanitize_text_field( wp_unslash( $input ) );
	}

	/**
	 * Sanitize email input
	 *
	 * @param string $email The email to sanitize.
	 * @return string The sanitized email.
	 */
	public static function sanitize_email_input( $email ) {
		return sanitize_email( wp_unslash( $email ) );
	}

	/**
	 * Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 */
	private function __wakeup() {}
}
