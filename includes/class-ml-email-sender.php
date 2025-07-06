<?php
/**
 * Email sender functionality for ML Mailing Lists
 *
 * @package ML_Mailing_Lists
 * @subpackage Email
 * @since 1.0.1
 */

namespace ML_Mailing_Lists;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Email_Sender class
 *
 * Handles email sending functionality.
 */
class Email_Sender {

	/**
	 * Unique instance of the class
	 *
	 * @var Email_Sender
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
	 * @return Email_Sender
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize email functionality
	 */
	private function init() {
		// Email functionality is handled by other classes when needed.
	}

	/**
	 * Send email to a single recipient
	 *
	 * @param string $to      Recipient email address.
	 * @param string $subject Email subject.
	 * @param string $message Email content.
	 * @param array  $headers Optional. Email headers.
	 * @return bool True if sent successfully, false otherwise.
	 */
	public function send_email( $to, $subject, $message, $headers = array() ) {
		if ( ! is_email( $to ) ) {
			return false;
		}

		// Set default headers.
		if ( empty( $headers ) ) {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		}

		return wp_mail( $to, $subject, $message, $headers );
	}

	/**
	 * Send bulk emails to multiple recipients
	 *
	 * @param array  $recipients Array of recipient email addresses.
	 * @param string $subject    Email subject.
	 * @param string $message    Email content template.
	 * @param array  $headers    Optional. Email headers.
	 * @return int Number of emails sent successfully.
	 */
	public function send_bulk_emails( $recipients, $subject, $message, $headers = array() ) {
		$sent_count = 0;

		if ( empty( $headers ) ) {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		}

		foreach ( $recipients as $recipient ) {
			if ( is_array( $recipient ) ) {
				$email                = $recipient['email'];
				$personalized_message = $this->personalize_message( $message, $recipient );
			} else {
				$email                = $recipient;
				$personalized_message = $message;
			}

			if ( $this->send_email( $email, $subject, $personalized_message, $headers ) ) {
				++$sent_count;
			}

			// Add a small delay to prevent overwhelming the mail server.
			usleep( 100000 ); // 0.1 seconds.
		}

		return $sent_count;
	}

	/**
	 * Personalize email message with subscriber data
	 *
	 * @param string $message    Email message template.
	 * @param array  $subscriber Subscriber data.
	 * @return string Personalized message.
	 */
	private function personalize_message( $message, $subscriber ) {
		$replacements = array(
			'{{nome}}'    => isset( $subscriber['nome'] ) ? $subscriber['nome'] : '',
			'{{apelido}}' => isset( $subscriber['apelido'] ) ? $subscriber['apelido'] : '',
			'{{correo}}'  => isset( $subscriber['correo'] ) ? $subscriber['correo'] : '',
		);

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $message );
	}

	/**
	 * Validate email configuration
	 *
	 * @return bool True if email is properly configured, false otherwise.
	 */
	public function is_email_configured() {
		// Check if WordPress can send emails.
		$test_email = wp_mail(
			get_option( 'admin_email' ),
			'Correo de proba',
			'Este é un correo de proba para verificar a configuración do correo.',
			array( 'Content-Type: text/plain; charset=UTF-8' )
		);

		return $test_email;
	}

	/**
	 * Get email sending statistics
	 *
	 * @return array Statistics array.
	 */
	public function get_email_stats() {
		$logs = get_option( 'ml_bulk_email_logs', array() );

		$stats = array(
			'total_campaigns'    => count( $logs ),
			'total_emails_sent'  => array_sum( wp_list_pluck( $logs, 'sent_count' ) ),
			'last_campaign_date' => ! empty( $logs ) ? end( $logs )['date'] : null,
		);

		return $stats;
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
