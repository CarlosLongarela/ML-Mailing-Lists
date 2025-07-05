<?php
/**
 * Plugin Name: ML Mailing Lists
 * Description: Plugin para gestionar listas de correo y suscripciones.
 * Version: 1.0.1
 * Author: Carlos Longarela
 * Author URI: https://tabernawp.com/
 * License: GPL2
 * Text Domain: ml-mailing-lists
 * Domain Path: /languages
 * ML Plugin URI:
 *
 * @package ML Mailing Lists
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode to show a subscription form for a mailing list.
 * Usage: [ml_subscription_form list_id="123" title="Suscríbete a nuestra lista" btn_text="Suscribirse" css_class="ml-subscription-form"]
 *
 * @param array $atts Shortcode attributes.
 *
 * @return string HTML output of the subscription form.
 */
function ml_subscription_form_shortcode( $atts ) {
	// Shortcode attributes with defaults.
	$atts = shortcode_atts(
		array(
			'list_id'   => '',
			'title'     => 'Suscríbete a nuestra lista',
			'btn_text'  => 'Suscribirse',
			'css_class' => 'ml-subscription-form',
		),
		$atts
	);

	// Check if the list ID is provided.
	if ( empty( $atts['list_id']) ) {
		return '<p style="color: red;">Error: Debe especificar el ID de la lista.</p>';
	}

	// Check if the taxonomy 'ml_lista' exists..
	$term = get_term( $atts['list_id'], 'ml_lista' );

	if ( is_wp_error( $term ) || ! $term ) {
		return '<p style="color: red;">Error: La lista especificada no existe.</p>';
	}

	// Generate a unique ID for the form.
	$form_id = 'ml-form-' . $atts['list_id'];

	// Process the form if it has been submitted.
	$message = '';

	if ( isset( $_POST['ml_submit'] ) && $atts['list_id'] === $_POST['ml_list_id'] ) {
		$message = ml_process_subscription_form();
	}

	// Generate a nonce for security.
	$nonce = wp_create_nonce( 'ml_subscription_' . $atts['list_id'] );

	// Form HTML output.
	ob_start();
	?>
	<div class="<?php echo esc_attr( $atts['css_class'] ); ?>">
		<?php if ( ! empty( $message ) ) : ?>
			<div class="ml-message"><?php echo $message; ?></div>
		<?php endif; ?>

		<form id="<?php echo esc_attr( $form_id ); ?>" method="post" action="">
			<h3><?php echo esc_html( $atts['title'] ); ?></h3>

			<div class="ml-field">
				<label for="ml_name_<?php echo esc_attr( $atts['list_id'] ); ?>">Nombre *</label>
				<input type="text"
					id="ml_name_<?php echo esc_attr( $atts['list_id'] ); ?>"
					name="ml_name"
					required
					value="<?php echo isset( $_POST['ml_name'] ) ? esc_attr( $_POST['ml_name'] ) : ''; ?>">
			</div>

			<div class="ml-field">
				<label for="ml_surname_<?php echo esc_attr( $atts['list_id'] ); ?>">Apellidos *</label>
				<input type="text"
					id="ml_surname_<?php echo esc_attr( $atts['list_id'] ); ?>"
					name="ml_surname"
					required
					value="<?php echo isset( $_POST['ml_surname'] ) ? esc_attr( $_POST['ml_surname'] ) : ''; ?>">
			</div>

			<div class="ml-field">
				<label for="ml_mail_<?php echo esc_attr( $atts['list_id'] ); ?>">Email *</label>
				<input type="email"
					id="ml_mail_<?php echo esc_attr( $atts['list_id'] ); ?>"
					name="ml_mail"
					required
					value="<?php echo isset( $_POST['ml_mail'] ) ? esc_attr( $_POST['ml_mail'] ) : ''; ?>">
			</div>

			<input type="hidden" name="ml_list_id" value="<?php echo esc_attr( $atts['list_id'] ); ?>">
			<input type="hidden" name="ml_nonce" value="<?php echo esc_attr( $nonce ); ?>">

			<div class="ml-field">
				<button type="submit" name="ml_submit" class="ml-submit-btn">
					<?php echo esc_html( $atts['btn_text'] ); ?>
				</button>
			</div>

			<div class="ml-honeypot">
				<label for="ml_honeypot_<?php echo esc_attr( $atts['list_id'] ); ?>" style="display:none;">Si usted es humano, deje este campo vacío.</label>
				<input type="text" id="ml_honeypot_<?php echo esc_attr( $atts['list_id'] ); ?>" name="ml_honeypot" value="" style="display:none;">
			</div>
		</form>
	</div>

	<?php
	echo ml_get_subscription_form_css( $atts['css_class'] );

	return ob_get_clean();
}

/**
 * Get the CSS styles for the subscription form.
 *
 * @param string $css_class The CSS class for the form.
 * @return string CSS styles.
 */
function ml_get_subscription_form_css( $css_class ) {
	static $css_printed = false;

	// Only print CSS once per page load.
	if ( $css_printed ) {
		return '';
	}

	$css_printed = true;

	ob_start();
	?>
	<style>
	:root {
		--ml-primary-color: #0073aa;
		--ml-secondary-color: #005a87;
		--ml-text-color: #333;
		--ml-background-color: #f9f9f9;
		--ml-border-color: #ddd;
		--ml-success-bg: #d4edda;
		--ml-success-text: #155724;
		--ml-error-bg: #f8d7da;
		--ml-error-text: #721c24;
	}

	.<?php echo esc_attr( $css_class ); ?> {
		max-width: 500px;
		margin: 20px 0;
		padding: 20px;
		border: 1px solid var(--ml-border-color);
		border-radius: 5px;
		background-color: var(--ml-background-color);
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
	}

	.<?php echo esc_attr( $css_class ); ?> h3 {
		margin-top: 0;
		color: var(--ml-text-color);
	}

	.ml-field {
		margin-bottom: 15px;
	}

	.ml-field label {
		display: block;
		margin-bottom: 5px;
		font-weight: bold;
		color: #555555;
	}

	.ml-field input[type="text"],
	.ml-field input[type="email"] {
		width: 100%;
		padding: 8px 12px;
		border: 1px solid var(--ml-border-color);
		border-radius: 4px;
		font-size: 14px;
		box-sizing: border-box;
		transition: border-color 0.3s, box-shadow 0.3s;
	}

	.ml-field input[type="text"]:focus,
	.ml-field input[type="email"]:focus {
		outline: none;
		border-color: var(--ml-primary-color);
		box-shadow: 0 0 5px rgba(0, 115, 170, 0.3);
	}

	.ml-submit-btn {
		background-color: var(--ml-primary-color);
		color: white;
		padding: 10px 20px;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		font-size: 16px;
		transition: background-color 0.3s;
		width: 100%;
		max-width: 200px;
	}

	.ml-submit-btn:hover {
		background-color: var(--ml-secondary-color);
	}

	.ml-submit-btn:disabled {
		background-color: #ccc;
		cursor: not-allowed;
	}

	.ml-message, .ml-mensaje {
		padding: 10px;
		margin-bottom: 15px;
		border-radius: 4px;
		font-weight: 500;
	}

	.ml-message.success, .ml-mensaje.success {
		background-color: var(--ml-success-bg);
		color: var(--ml-success-text);
		border: 1px solid var(--ml-success-bg);
	}

	.ml-message.error, .ml-mensaje.error {
		background-color: var(--ml-error-bg);
		color: var(--ml-error-text);
		border: 1px solid var(--ml-error-bg);
	}

	.ml-privacy-notice {
		font-size: 12px;
		color: #666;
		margin-top: 10px;
		line-height: 1.4;
	}

	.ml-honeypot {
		position: absolute !important;
		left: -9999px !important;
		width: 1px !important;
		height: 1px !important;
		overflow: hidden !important;
	}

	@media (max-width: 768px) {
		.<?php echo esc_attr( $css_class ); ?> {
			margin: 10px 0;
			padding: 15px;
		}
	}
	</style>
	<?php
	return ob_get_clean();
}

/**
 * Process the subscription form submission.
 */
function ml_process_subscription_form() {
	// Check if required POST variables exist
	if ( ! isset( $_POST['ml_nonce'], $_POST['ml_list_id'], $_POST['ml_name'], $_POST['ml_surname'], $_POST['ml_mail'] ) ) {
		return '<div class="ml-mensaje error">Datos del formulario incompletos.</div>';
	}

	// Verify nonce for security.
	$list_id_post = intval( $_POST['ml_list_id'] );
	$nonce        = sanitize_text_field( wp_unslash( $_POST['ml_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'ml_subscription_' . $list_id_post ) ) {
		return '<div class="ml-mensaje error">Error de seguridad. Por favor, inténtelo de nuevo.</div>';
	}

	// Add rate limiting check (max 3 submissions per IP per hour).
	$user_ip        = $_SERVER['REMOTE_ADDR'] ?? '';
	$rate_limit_key = 'ml_rate_limit_' . md5( $user_ip );
	$current_count  = get_transient( $rate_limit_key );

	if ( $current_count && $current_count >= 3 ) {
		return '<div class="ml-mensaje error">Demasiados intentos. Por favor, espere una hora antes de volver a intentarlo.</div>';
	}

	// Sanitize input data.
	$name    = sanitize_text_field( wp_unslash( $_POST['ml_name'] ) );
	$surname = sanitize_text_field( wp_unslash( $_POST['ml_surname'] ) );
	$email   = sanitize_email( wp_unslash( $_POST['ml_mail'] ) );
	$list_id = $list_id_post;

	// Validation checks.
	if ( empty( $name ) || empty( $surname ) || empty( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, complete todos los campos obligatorios.</div>';
	}

	if ( ! is_email( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, introduzca un email válido.</div>';
	}

	// Check if email already exists in the specific list.
	if ( ml_subscription_exists( $email, $list_id ) ) {
		return '<div class="ml-mensaje error">Este email ya está suscrito a esta lista.</div>';
	}

	// Apply filters to allow customization.
	$name    = apply_filters( 'ml_subscription_name', $name, $list_id );
	$surname = apply_filters( 'ml_subscription_surname', $surname, $list_id );
	$email   = apply_filters( 'ml_subscription_email', $email, $list_id );

	// Create the post for the subscription.
	$post_data = array(
		'post_type'   => 'ml_mailing_lists',
		'post_title'  => $name . ' ' . $surname,
		'post_status' => 'publish',
		'meta_input'  => array(
			'ml_name'              => $name,
			'ml_surname'           => $surname,
			'ml_email'             => $email,
			'ml_subscription_date' => current_time( 'mysql' ),
			'ml_ip_address'        => $user_ip,
		),
	);

	$post_id = wp_insert_post( $post_data );

	if ( is_wp_error( $post_id ) ) {
		return '<div class="ml-mensaje error">Error al procesar la suscripción. Por favor, inténtelo de nuevo.</div>';
	}

	// Assign taxonomy term to the post.
	wp_set_post_terms( $post_id, array( $list_id ), 'ml_lista' );

	// Update rate limiting counter.
	$new_count = $current_count ? $current_count + 1 : 1;
	set_transient( $rate_limit_key, $new_count, HOUR_IN_SECONDS );

	// Fire action hook for extensibility.
	do_action( 'ml_subscription_created', $post_id, $email, $list_id );

	// Clear POST variables to prevent resubmission.
	unset( $_POST['ml_name'], $_POST['ml_surname'], $_POST['ml_mail'] );

	return '<div class="ml-mensaje success">¡Gracias! Su suscripción se ha procesado correctamente.</div>';
}

/**
 * Validate subscription form data.
 *
 * @param array $data The form data.
 * @return array Array with 'valid' boolean and 'message' string.
 */
function ml_validate_subscription_data( $data ) {
	$errors = array();

	// Basic validation.
	if ( empty( $data['name'] ) ) {
		$errors[] = 'El nombre es obligatorio.';
	} elseif ( strlen( $data['name'] ) > 100 ) {
		$errors[] = 'El nombre no puede exceder 100 caracteres.';
	}

	if ( empty( $data['surname'] ) ) {
		$errors[] = 'Los apellidos son obligatorios.';
	} elseif ( strlen( $data['surname'] ) > 100 ) {
		$errors[] = 'Los apellidos no pueden exceder 100 caracteres.';
	}

	if ( empty( $data['email'] ) ) {
		$errors[] = 'El email es obligatorio.';
	} elseif ( ! is_email( $data['email'] ) ) {
		$errors[] = 'Por favor, introduzca un email válido.';
	} elseif ( strlen( $data['email'] ) > 255 ) {
		$errors[] = 'El email no puede exceder 255 caracteres.';
	}

	// Check for suspicious patterns (basic spam detection).
	$suspicious_patterns = array( 'http://', 'https://', 'www.', '.com', '.net', '.org' );
	foreach ( $suspicious_patterns as $pattern ) {
		if ( stripos( $data['name'], $pattern ) !== false || stripos( $data['surname'], $pattern ) !== false ) {
			$errors[] = 'Los datos introducidos parecen contener contenido spam.';
			break;
		}
	}

	return array(
		'valid'   => empty( $errors ),
		'message' => empty( $errors ) ? '' : implode( ' ', $errors ),
	);
}

/**
 * Check if subscription already exists.
 *
 * @param string $email   The email address.
 * @param int    $list_id The list ID.
 * @return bool True if subscription exists, false otherwise.
 */
function ml_subscription_exists( $email, $list_id ) {
	$existing_posts = get_posts(
		array(
			'post_type'      => 'ml_mailing_lists',
			'meta_query'     => array(
				array(
					'key'     => 'ml_email',
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
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	return ! empty( $existing_posts );
}

/**
 * Get user IP address safely.
 *
 * @return string The user's IP address.
 */
function ml_get_user_ip() {
	// Check for various headers that might contain the real IP.
	$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );

	foreach ( $ip_keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			// Handle comma-separated IPs (forwarded headers).
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

// Register the shortcode.
add_shortcode( 'ml_subscription_form', 'ml_subscription_form_shortcode' );

/**
 * Add export functionality to the mailing lists admin page.
 */
add_action( 'admin_init', 'ml_handle_export_requests' );
add_action( 'restrict_manage_posts', 'ml_add_export_buttons' );
add_action( 'admin_head', 'ml_add_export_buttons_styles' );

/**
 * Handle export requests for CSV and TXT formats.
 */
function ml_handle_export_requests() {
	// Check if this is an export request.
	if ( ! isset( $_GET['ml_export'] ) || ! in_array( $_GET['ml_export'], array( 'csv', 'txt' ) ) ) {
		return;
	}

	// Verify nonce for security.
	if ( ! isset( $_GET['ml_export_nonce'] ) || ! wp_verify_nonce( $_GET['ml_export_nonce'], 'ml_export_emails' ) ) {
		wp_die( 'Error de seguridad. Acceso denegado.' );
	}

	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'No tienes permisos para realizar esta acción.' );
	}

	$format  = sanitize_text_field( $_GET['ml_export'] );
	$list_id = isset( $_GET['ml_list_filter'] ) ? intval( $_GET['ml_list_filter'] ) : 0;

	// Get emails based on filter.
	$emails = ml_get_emails_for_export( $list_id );

	if ( empty( $emails ) ) {
		wp_die( 'No se encontraron emails para exportar.' );
	}

	// Generate filename.
	$date      = gmdate( 'Y-m-d_H-i-s' );
	$list_name = '';

	if ( $list_id > 0 ) {
		$term      = get_term( $list_id, 'ml_lista' );
		$list_name = $term && ! is_wp_error( $term ) ? '_' . sanitize_file_name( $term->name ) : '';
	}

	$filename = 'mailing_list' . $list_name . '_' . $date . '.' . $format;

	// Set headers for download.
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// Output based on format.
	if ( 'csv'=== $format ) {
		ml_output_csv( $emails );
	} else {
		ml_output_txt( $emails );
	}

	exit;
}

/**
 * Get emails for export based on list filter.
 *
 * @param int $list_id The list ID to filter by (0 for all lists).
 * @return array Array of email data.
 */
function ml_get_emails_for_export( $list_id = 0 ) {
	$args = array(
		'post_type'      => 'ml_mailing_lists',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => 'ml_email',
				'compare' => 'EXISTS',
			),
		),
	);

	// Add taxonomy filter if specific list is selected.
	if ( $list_id > 0 ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'ml_lista',
				'field'    => 'term_id',
				'terms'    => $list_id,
			),
		);
	}

	$posts  = get_posts( $args );
	$emails = array();

	foreach ( $posts as $post ) {
		$name              = get_post_meta( $post->ID, 'ml_name', true );
		$surname           = get_post_meta( $post->ID, 'ml_surname', true );
		$email             = get_post_meta( $post->ID, 'ml_email', true );
		$subscription_date = get_post_meta( $post->ID, 'ml_subscription_date', true );

		// Get list names.
		$terms      = wp_get_post_terms( $post->ID, 'ml_lista' );
		$list_names = array();
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$list_names[] = $term->name;
			}
		}

		$emails[] = array(
			'name'              => $name,
			'surname'           => $surname,
			'email'             => $email,
			'subscription_date' => $subscription_date ? $subscription_date : $post->post_date,
			'lists'             => implode( ', ', $list_names ),
		);
	}

	return $emails;
}

/**
 * Output emails in CSV format.
 *
 * @param array $emails Array of email data.
 */
function ml_output_csv( $emails ) {
	// Open output stream.
	$output = fopen( 'php://output', 'w' );

	// Add BOM for UTF-8.
	fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

	// Add CSV headers.
	fputcsv( $output, array( 'Nombre', 'Apellidos', 'Email', 'Fecha Suscripción', 'Listas' ), ';' );

	// Add data rows.
	foreach ( $emails as $email_data ) {
		fputcsv(
			$output,
			array(
				$email_data['name'],
				$email_data['surname'],
				$email_data['email'],
				$email_data['subscription_date'],
				$email_data['lists'],
			),
			';',
		);
	}

	fclose( $output );
}

/**
 * Output emails in TXT format.
 *
 * @param array $emails Array of email data.
 */
function ml_output_txt( $emails ) {
	echo "LISTA DE CORREOS ELECTRÓNICOS\n";
	echo "=============================\n";
	echo 'Exportado el: ' . gmdate( 'd/m/Y H:i:s' ) . "\n";
	echo 'Total de emails: ' . count( $emails ) . "\n\n";

	foreach ( $emails as $email_data ) {
		echo 'Nombre: ' . $email_data['name'] . ' ' . $email_data['surname'] . "\n";
		echo 'Email: ' . $email_data['email'] . "\n";
		echo 'Fecha: ' . $email_data['subscription_date'] . "\n";
		echo 'Listas: ' . $email_data['lists'] . "\n";
		echo "------------------------\n";
	}
}

/**
 * Add export buttons to the admin posts list page.
 */
function ml_add_export_buttons() {
	global $typenow;

	// Only show on mailing lists post type.
	if ( 'ml_mailing_lists' !== $typenow ) {
		return;
	}

	$export_nonce = wp_create_nonce( 'ml_export_emails' );
	$current_url  = remove_query_arg( array( 'ml_export', 'ml_export_nonce', 'ml_list_filter' ) );

	// Get current list filter if any.
	$current_list = isset( $_GET['ml_lista'] ) ? intval( $_GET['ml_lista'] ) : 0;

	// Build export URLs.
	$csv_url = add_query_arg(
		array(
			'ml_export'        => 'csv',
			'ml_export_nonce'  => $export_nonce,
			'ml_list_filter'   => $current_list,
		),
		$current_url
	);

	$txt_url = add_query_arg(
		array(
			'ml_export'       => 'txt',
			'ml_export_nonce' => $export_nonce,
			'ml_list_filter'  => $current_list,
		),
		$current_url
	);

	?>
	<div class="ml-export-buttons">
		<a href="<?php echo esc_url( $csv_url ); ?>" class="button button-primary ml-export-csv">
			📊 Exportar CSV
		</a>
		<a href="<?php echo esc_url( $txt_url ); ?>" class="button button-secondary ml-export-txt">
			📄 Exportar TXT
		</a>
	</div>
	<?php
}

/**
 * Add CSS styles for export buttons.
 */
function ml_add_export_buttons_styles() {
	global $pagenow, $typenow;

	// Only add on the mailing lists admin page.
	if ( 'edit.php' !== $pagenow || 'ml_mailing_lists' !== $typenow ) {
		return;
	}
	?>
	<style type="text/css">
	:root {
		--ml-export-csv-bg: #00a32a;
		--ml-export-csv-hover-bg: #008a20;
		--ml-export-txt-bg: #2271b1;
		--ml-export-txt-hover-bg: #135e96;
	}

	.ml-export-buttons {
		display: inline-block;
		margin-left: 8px;
		vertical-align: top;
	}

	.ml-export-buttons .button {
		height: 30px;
		line-height: 28px;
		padding: 0 12px;
		font-size: 13px;
		margin-right: 4px;
		text-decoration: none;
	}

	.ml-export-csv {
		background-color: var(--ml-export-csv-bg) !important;
		border-color: var(--ml-export-csv-bg) !important;
		color: white !important;
	}

	.ml-export-csv:hover,
	.ml-export-csv:focus {
		background-color: var(--ml-export-csv-hover-bg) !important;
		border-color: var(--ml-export-csv-hover-bg) !important;
		color: white !important;
	}

	.ml-export-txt {
		background-color: var(--ml-export-txt-bg) !important;
		border-color: var(--ml-export-txt-bg) !important;
		color: white !important;
	}

	.ml-export-txt:hover,
	.ml-export-txt:focus {
		background-color: var(--ml-export-txt-hover-bg) !important;
		border-color: var(--ml-export-txt-hover-bg) !important;
		color: white !important;
	}

	@media screen and (max-width: 782px) {
		.ml-export-buttons {
			margin: 5px 0;
			display: block;
		}

		.ml-export-buttons .button {
			margin-bottom: 5px;
			display: inline-block;
		}
	}
	</style>
	<?php
}

/**
 * Add admin notice for successful exports.
 */
function ml_export_admin_notices() {
	if ( isset( $_GET['ml_exported'] ) && '1' === $_GET['ml_exported'] ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>ML Mailing Lists:</strong> La exportación se ha completado correctamente.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ml_export_admin_notices' );
