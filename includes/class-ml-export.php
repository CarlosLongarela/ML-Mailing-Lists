<?php
/**
 * Export functionality for ML Mailing Lists
 *
 * @package ML_Mailing_Lists
 * @subpackage Export
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ML_Export
 *
 * Handles data export functionality.
 */
class ML_Export {

	/**
	 * Unique instance of the class
	 *
	 * @var ML_Export
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
	 * @return ML_Export
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize export functionality
	 */
	private function init() {
		// Export functionality is handled by admin class when needed.
	}

	/**
	 * Export subscribers data
	 *
	 * @param int    $list_id Optional. Specific list ID to export.
	 * @param string $format  Export format (csv or txt).
	 * @return array Export data.
	 */
	public function export_subscribers( $list_id = 0, $format = 'csv' ) {
		// Build query args.
		$args = array(
			'post_type'      => 'ml_suscriptor',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		if ( $list_id > 0 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'ml_lista',
					'field'    => 'term_id',
					'terms'    => $list_id,
				),
			);
		}

		$subscribers = get_posts( $args );
		$export_data = array();

		foreach ( $subscribers as $subscriber ) {
			$export_data[] = array(
				'id'                => $subscriber->ID,
				'nome'              => get_post_meta( $subscriber->ID, 'nome', true ),
				'apelido'           => get_post_meta( $subscriber->ID, 'apelido', true ),
				'correo'            => get_post_meta( $subscriber->ID, 'correo', true ),
				'data_subscripcion' => get_post_meta( $subscriber->ID, 'data_subscripcion', true ),
				'lists'             => $this->get_subscriber_lists( $subscriber->ID ),
			);
		}

		return $export_data;
	}

	/**
	 * Get subscriber's mailing lists
	 *
	 * @param int $subscriber_id Subscriber post ID.
	 * @return array Array of list names.
	 */
	private function get_subscriber_lists( $subscriber_id ) {
		$lists = get_the_terms( $subscriber_id, 'ml_lista' );

		if ( ! $lists || is_wp_error( $lists ) ) {
			return array();
		}

		return wp_list_pluck( $lists, 'name' );
	}

	/**
	 * Generate CSV content
	 *
	 * @param array $data Export data.
	 * @return string CSV content.
	 */
	public function generate_csv( $data ) {
		if ( empty( $data ) ) {
			return '';
		}

		$output = "Nome,Apelido,Correo,Data Subscrición,Listas\n";

		foreach ( $data as $row ) {
			$lists_string = is_array( $row['lists'] ) ? implode( '; ', $row['lists'] ) : '';

			$output .= sprintf(
				'"%s","%s","%s","%s","%s"' . "\n",
				esc_attr( $row['nome'] ),
				esc_attr( $row['apelido'] ),
				esc_attr( $row['correo'] ),
				esc_attr( $row['data_subscripcion'] ),
				esc_attr( $lists_string )
			);
		}

		return $output;
	}

	/**
	 * Generate TXT content
	 *
	 * @param array $data Export data.
	 * @return string TXT content.
	 */
	public function generate_txt( $data ) {
		if ( empty( $data ) ) {
			return '';
		}

		$output = '';

		foreach ( $data as $row ) {
			$output .= sprintf(
				"%s %s <%s>\n",
				esc_attr( $row['nome'] ),
				esc_attr( $row['apelido'] ),
				esc_attr( $row['correo'] )
			);
		}

		return $output;
	}

	/**
	 * Download export file
	 *
	 * @param array  $data     Export data.
	 * @param string $format   File format.
	 * @param string $filename Optional. Custom filename.
	 */
	public function download_export( $data, $format, $filename = '' ) {
		if ( empty( $filename ) ) {
			$filename = 'suscriptores-' . date( 'Y-m-d' ) . '.' . $format;
		}

		// Set headers for file download.
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		if ( 'csv' === $format ) {
			echo $this->generate_csv( $data );
		} else {
			echo $this->generate_txt( $data );
		}

		exit;
	}

	/**
	 * Get export statistics
	 *
	 * @return array Statistics array.
	 */
	public function get_export_stats() {
		$total_subscribers = wp_count_posts( 'ml_suscriptor' )->publish;

		$lists = get_terms(
			array(
				'taxonomy'   => 'ml_lista',
				'hide_empty' => false,
			)
		);

		$stats = array(
			'total_subscribers' => $total_subscribers,
			'total_lists'       => count( $lists ),
			'lists_breakdown'   => array(),
		);

		foreach ( $lists as $list ) {
			$stats['lists_breakdown'][ $list->name ] = $list->count;
		}

		return $stats;
	}

	/**
	 * Validate export parameters
	 *
	 * @param int    $list_id List ID.
	 * @param string $format  Export format.
	 * @return array Validation result.
	 */
	public function validate_export_params( $list_id, $format ) {
		$errors = array();

		// Validate format.
		if ( ! in_array( $format, array( 'csv', 'txt' ), true ) ) {
			$errors[] = 'Formato de exportación non válido.';
		}

		// Validate list ID if provided.
		if ( $list_id > 0 ) {
			$term = get_term( $list_id, 'ml_lista' );
			if ( is_wp_error( $term ) || ! $term ) {
				$errors[] = 'A lista especificada non existe.';
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
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
