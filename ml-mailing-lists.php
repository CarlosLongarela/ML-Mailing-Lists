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
			'title'     => 'Subscríbete á nosa lista',
			'btn_text'  => 'Subscribirse',
			'css_class' => 'ml-subscription-form',
		),
		$atts
	);

	// Check if the list ID is provided.
	if ( empty( $atts['list_id']) ) {
		return '<p style="color: red;">Erro: Debe especificar o ID da lista.</p>';
	}

	// Check if the taxonomy 'ml_lista' exists..
	$term = get_term( $atts['list_id'], 'ml_lista' );

	if ( is_wp_error( $term ) || ! $term ) {
		return '<p style="color: red;">Erro: A lista especificada non existe.</p>';
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
				<label for="ml_name_<?php echo esc_attr( $atts['list_id'] ); ?>">Nome *</label>
				<input type="text"
					id="ml_name_<?php echo esc_attr( $atts['list_id'] ); ?>"
					name="ml_name"
					required
					value="<?php echo isset( $_POST['ml_name'] ) ? esc_attr( $_POST['ml_name'] ) : ''; ?>">
			</div>

			<div class="ml-field">
				<label for="ml_surname_<?php echo esc_attr( $atts['list_id'] ); ?>">Apelidos *</label>
				<input type="text"
					id="ml_surname_<?php echo esc_attr( $atts['list_id'] ); ?>"
					name="ml_surname"
					required
					value="<?php echo isset( $_POST['ml_surname'] ) ? esc_attr( $_POST['ml_surname'] ) : ''; ?>">
			</div>

			<div class="ml-field">
				<label for="ml_mail_<?php echo esc_attr( $atts['list_id'] ); ?>">Correo *</label>
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
				<label for="ml_honeypot_<?php echo esc_attr( $atts['list_id'] ); ?>" style="display:none;">Se es humano, deixe este campo baleiro.</label>
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
		return '<div class="ml-mensaje error">Datos do formulario incompletos.</div>';
	}

	// Verify nonce for security.
	$list_id_post = intval( $_POST['ml_list_id'] );
	$nonce        = sanitize_text_field( wp_unslash( $_POST['ml_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'ml_subscription_' . $list_id_post ) ) {
		return '<div class="ml-mensaje error">Erro de seguridade. Por favor, inténteo de novo.</div>';
	}

	// Add rate limiting check (max 3 submissions per IP per hour).
	$user_ip        = $_SERVER['REMOTE_ADDR'] ?? '';
	$rate_limit_key = 'ml_rate_limit_' . md5( $user_ip );
	$current_count  = get_transient( $rate_limit_key );

	if ( $current_count && $current_count >= 3 ) {
		return '<div class="ml-mensaje error">Demasiados intentos. Por favor, agarde unha hora antes de volver intentalo.</div>';
	}

	// Sanitize input data.
	$name    = sanitize_text_field( wp_unslash( $_POST['ml_name'] ) );
	$surname = sanitize_text_field( wp_unslash( $_POST['ml_surname'] ) );
	$email   = sanitize_email( wp_unslash( $_POST['ml_mail'] ) );
	$list_id = $list_id_post;

	// Validation checks.
	if ( empty( $name ) || empty( $surname ) || empty( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, complete todos os campos obrigatorios.</div>';
	}

	if ( ! is_email( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, introduza un correo válido.</div>';
	}

	// Check if email already exists in the specific list.
	if ( ml_subscription_exists( $email, $list_id ) ) {
		return '<div class="ml-mensaje error">Este correo xa está subscrito a esta lista.</div>';
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
		return '<div class="ml-mensaje error">Erro ao procesar a subscrición. Por favor, inténteo de novo.</div>';
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

	return '<div class="ml-mensaje success">¡Grazas! A súa subscrición procesouse correctamente.</div>';
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

	// Check for suspicious patterns (basic spam detection).
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
		wp_die( 'Erro de seguridade. Acceso denegado.' );
	}

	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non tes permisos para realizar esta acción.' );
	}

	$format  = sanitize_text_field( $_GET['ml_export'] );
	$list_id = isset( $_GET['ml_list_filter'] ) ? intval( $_GET['ml_list_filter'] ) : 0;

	// Get emails based on filter.
	$emails = ml_get_emails_for_export( $list_id );

	if ( empty( $emails ) ) {
		wp_die( 'Non se atoparon correos para exportar.' );
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
	fputcsv( $output, array( 'Nome', 'Apelido', 'Correo', 'Data Subscrición', 'Listas' ), ';' );

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
	echo 'Exportado o: ' . gmdate( 'd/m/Y H:i:s' ) . "\n";
	echo 'Total de correos: ' . count( $emails ) . "\n\n";

	foreach ( $emails as $email_data ) {
		echo 'Nome: ' . $email_data['name'] . ' ' . $email_data['surname'] . "\n";
		echo 'Correo: ' . $email_data['email'] . "\n";
		echo 'Data: ' . $email_data['subscription_date'] . "\n";
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
			<p><strong>ML Mailing Lists:</strong> A exportación completouse correctamente.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ml_export_admin_notices' );

/**
 * Add email sending functionality to admin.
 */
add_action( 'admin_menu', 'ml_add_email_sender_page' );
add_action( 'admin_init', 'ml_handle_email_sending' );
add_action( 'admin_enqueue_scripts', 'ml_enqueue_admin_scripts' );

/**
 * Add email sender submenu page.
 */
function ml_add_email_sender_page() {
	add_submenu_page(
		'edit.php?post_type=ml_mailing_lists',
		'Enviar Correo',
		'Enviar Correo',
		'manage_options',
		'ml-send-email',
		'ml_email_sender_page_callback'
	);
}

/**
 * Callback for the email sender page.
 */
function ml_email_sender_page_callback() {
	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non tes permisos para acceder a esta páxina.' );
	}

	// Get all lists for the dropdown.
	$lists = get_terms(
		array(
			'taxonomy'   => 'ml_lista',
			'hide_empty' => false,
		)
	);

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php
		// Show admin notices.
		if ( isset( $_GET['message'] ) ) {
			$message_type = sanitize_text_field( $_GET['message'] );
			if ( 'sent' === $message_type ) {
				echo '<div class="notice notice-success is-dismissible"><p><strong>¡Éxito!</strong> O correo enviouse correctamente a todos os subscritores.</p></div>';
			} elseif ( 'error' === $message_type ) {
				echo '<div class="notice notice-error is-dismissible"><p><strong>Erro:</strong> Ocorreu un problema ao enviar o correo.</p></div>';
			} elseif ( 'no-subscribers' === $message_type ) {
				echo '<div class="notice notice-warning is-dismissible"><p><strong>Aviso:</strong> Non se atoparon subscritores na lista seleccionada.</p></div>';
			}
		}
		?>

		<form method="post" action="" id="ml-email-form">
			<?php wp_nonce_field( 'ml_send_email', 'ml_email_nonce' ); ?>

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="ml_email_list">Lista de destinatarios <span class="description">(obrigatorio)</span></label>
					</th>
					<td>
						<select name="ml_email_list" id="ml_email_list" class="regular-text" required>
							<option value="">-- Selecciona unha lista --</option>
							<?php if ( ! empty( $lists ) && ! is_wp_error( $lists ) ) : ?>
								<?php foreach ( $lists as $list ) : ?>								<option value="<?php echo esc_attr( $list->term_id ); ?>">
									<?php echo esc_html( $list->name ); ?>
									(<?php echo esc_html( $list->count ); ?> subscritores)
								</option>
								<?php endforeach; ?>
							<?php else : ?>
								<option value="" disabled>Non hai listas dispoñibles</option>
							<?php endif; ?>
						</select>
						<p class="description">Selecciona a lista á que queres enviar o correo.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="ml_email_from_name">Nome do remitente</label>
					</th>
					<td>
						<input type="text"
							name="ml_email_from_name"
							id="ml_email_from_name"
							class="regular-text"
							value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
							placeholder="O teu nome ou empresa">
						<p class="description">Nome que aparecerá como remitente do correo.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="ml_email_from_email">Correo do remitente</label>
					</th>
					<td>
						<input type="email"
							name="ml_email_from_email"
							id="ml_email_from_email"
							class="regular-text"
							value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
							placeholder="o.teu@correo.com"
							required>
						<p class="description">Enderezo de correo que aparecerá como remitente.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="ml_email_subject">Asunto do correo <span class="description">(obrigatorio)</span></label>
					</th>
					<td>
						<input type="text"
							name="ml_email_subject"
							id="ml_email_subject"
							class="widefat"
							placeholder="Escribe o asunto do correo..."
							required>
						<p class="description">O asunto que verán os destinatarios.</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="ml_email_content">Contido do correo <span class="description">(obrigatorio)</span></label>
					</th>
					<td>
						<?php
						wp_editor(
							'',
							'ml_email_content',
							array(
								'textarea_name' => 'ml_email_content',
								'textarea_rows' => 15,
								'teeny'         => false,
								'media_buttons' => true,
								'tinymce'       => array(
									'toolbar1' => 'bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
									'toolbar2' => 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
								),
								'quicktags'     => true,
							)
						);
						?>
						<p class="description">
							Podes usar HTML e texto enriquecido.
							<strong>Variables dispoñibles:</strong>
							<code>{{nome}}</code> - Nome do subscritor,
							<code>{{apelido}}</code> - Apelido do subscritor,
							<code>{{correo}}</code> - Correo do subscritor
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="ml_email_preview">Vista previa</label>
					</th>
					<td>
						<label for="ml_email_preview_check">
							<input type="checkbox" id="ml_email_preview_check" name="ml_email_preview" value="1">
							Enviar correo de proba antes do envío masivo
						</label>
						<p class="description">Enviarase unha copia ao teu correo antes do envío masivo.</p>
					</td>
				</tr>
			</table>

			<div class="ml-email-actions">
				<p class="submit">
					<input type="submit"
						name="ml_send_email"
						id="ml_send_email_btn"
						class="button button-primary button-large"
						value="📧 Enviar Correo">
					<span class="spinner" id="ml_email_spinner"></span>
				</p>

				<div class="ml-email-confirmation" id="ml_email_confirmation" style="display: none;">
					<div class="notice notice-warning">
						<p>
							<strong>⚠️ Confirmación necesaria</strong><br>
							Estás a punto de enviar un correo a <span id="ml_subscriber_count">0</span> subscritores da lista "<span id="ml_list_name"></span>".
							<br><br>
							<button type="button" class="button button-secondary" onclick="mlCancelEmail()">Cancelar</button>
							<button type="button" class="button button-primary" onclick="mlConfirmEmail()">Confirmar Envío</button>
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>

	<style>
	.ml-email-actions {
		margin-top: 20px;
		padding-top: 20px;
		border-top: 1px solid #ddd;
	}

	.ml-email-confirmation {
		max-width: 600px;
		margin-top: 15px;
	}

	.ml-email-confirmation .notice {
		padding: 15px;
	}

	#ml_email_spinner.is-active {
		float: none;
		margin-left: 10px;
		visibility: visible;
	}

	.form-table th {
		width: 200px;
	}

	.description {
		color: #666;
		font-style: italic;
	}

	.widefat {
		width: 100%;
		max-width: 600px;
	}
	</style>

	<script>
	let mlEmailConfirmationPending = false;

	document.getElementById('ml-email-form').addEventListener('submit', function(e) {
		if (!mlEmailConfirmationPending) {
			e.preventDefault();
			mlShowConfirmation();
		}
	});

	function mlShowConfirmation() {
		const listSelect = document.getElementById('ml_email_list');
		const selectedOption = listSelect.options[listSelect.selectedIndex];

		if (!selectedOption.value) {
			alert('Por favor, selecciona unha lista de destinatarios.');
			return;
		}

		const listName = selectedOption.text.split(' (')[0];
		const subscriberCount = selectedOption.text.match(/\((\d+) subscritores\)/);

		document.getElementById('ml_list_name').textContent = listName;
		document.getElementById('ml_subscriber_count').textContent = subscriberCount ? subscriberCount[1] : '0';
		document.getElementById('ml_email_confirmation').style.display = 'block';
		document.getElementById('ml_send_email_btn').style.display = 'none';
	}

	function mlCancelEmail() {
		document.getElementById('ml_email_confirmation').style.display = 'none';
		document.getElementById('ml_send_email_btn').style.display = 'inline-block';
		mlEmailConfirmationPending = false;
	}

	function mlConfirmEmail() {
		mlEmailConfirmationPending = true;
		document.getElementById('ml_email_spinner').classList.add('is-active');
		document.getElementById('ml-email-form').submit();
	}
	</script>
	<?php
}

/**
 * Handle email sending form submission.
 */
function ml_handle_email_sending() {
	// Check if this is an email sending request.
	if ( ! isset( $_POST['ml_send_email'] ) || ! isset( $_POST['ml_email_nonce'] ) ) {
		return;
	}

	// Verify nonce.
	if ( ! wp_verify_nonce( $_POST['ml_email_nonce'], 'ml_send_email' ) ) {
		wp_die( 'Erro de seguridade. Por favor, inténtao de novo.' );
	}

	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Non tes permisos para realizar esta acción.' );
	}

	// Sanitize and validate form data.
	$list_id     = isset( $_POST['ml_email_list'] ) ? intval( $_POST['ml_email_list'] ) : 0;
	$from_name   = isset( $_POST['ml_email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_email_from_name'] ) ) : get_bloginfo( 'name' );
	$from_email  = isset( $_POST['ml_email_from_email'] ) ? sanitize_email( wp_unslash( $_POST['ml_email_from_email'] ) ) : get_option( 'admin_email' );
	$subject     = isset( $_POST['ml_email_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['ml_email_subject'] ) ) : '';
	$content     = isset( $_POST['ml_email_content'] ) ? wp_kses_post( wp_unslash( $_POST['ml_email_content'] ) ) : '';
	$send_preview = isset( $_POST['ml_email_preview'] ) && '1' === $_POST['ml_email_preview'];

	// Validation.
	if ( empty( $list_id ) || empty( $subject ) || empty( $content ) ) {
		wp_safe_redirect( add_query_arg( 'message', 'error', admin_url( 'edit.php?post_type=ml_mailing_lists&page=ml-send-email' ) ) );
		exit;
	}

	// Get subscribers from the selected list.
	$subscribers = ml_get_list_subscribers( $list_id );

	if ( empty( $subscribers ) ) {
		wp_safe_redirect( add_query_arg( 'message', 'no-subscribers', admin_url( 'edit.php?post_type=ml_mailing_lists&page=ml-send-email' ) ) );
		exit;
	}

	// Send preview email if requested.
	if ( $send_preview ) {
		$preview_result = ml_send_preview_email( $from_name, $from_email, $subject, $content );
		if ( ! $preview_result ) {
			wp_safe_redirect( add_query_arg( 'message', 'error', admin_url( 'edit.php?post_type=ml_mailing_lists&page=ml-send-email' ) ) );
			exit;
		}
	}

	// Send emails to all subscribers.
	$sent_count = ml_send_bulk_emails( $subscribers, $from_name, $from_email, $subject, $content );

	// Log the email sending activity.
	ml_log_email_activity( $list_id, $subject, count( $subscribers ), $sent_count );

	// Redirect with success message.
	wp_safe_redirect( add_query_arg( 'message', 'sent', admin_url( 'edit.php?post_type=ml_mailing_lists&page=ml-send-email' ) ) );
	exit;
}

/**
 * Get subscribers from a specific list.
 *
 * @param int $list_id The list ID.
 * @return array Array of subscriber data.
 */
function ml_get_list_subscribers( $list_id ) {
	$posts = get_posts(
		array(
			'post_type'      => 'ml_mailing_lists',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'ml_lista',
					'field'    => 'term_id',
					'terms'    => $list_id,
				),
			),
			'meta_query'     => array(
				array(
					'key'     => 'ml_email',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	$subscribers = array();
	foreach ( $posts as $post ) {
		$email = get_post_meta( $post->ID, 'ml_email', true );
		if ( is_email( $email ) ) {
			$subscribers[] = array(
				'email'   => $email,
				'name'    => get_post_meta( $post->ID, 'ml_name', true ),
				'surname' => get_post_meta( $post->ID, 'ml_surname', true ),
			);
		}
	}

	return $subscribers;
}

/**
 * Send a preview email to the admin.
 *
 * @param string $from_name  From name.
 * @param string $from_email From email.
 * @param string $subject    Email subject.
 * @param string $content    Email content.
 * @return bool Success status.
 */
function ml_send_preview_email( $from_name, $from_email, $subject, $content ) {
	$admin_email = get_option( 'admin_email' );
	$preview_subject = '[VISTA PREVIA] ' . $subject;

	// Replace variables with sample data.
	$preview_content = str_replace(
		array( '{{nome}}', '{{apelido}}', '{{correo}}' ),
		array( 'Xoán', 'Pérez', 'xoan.perez@example.com' ),
		$content
	);

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
	);

	return wp_mail( $admin_email, $preview_subject, $preview_content, $headers );
}

/**
 * Send bulk emails to subscribers.
 *
 * @param array  $subscribers Array of subscriber data.
 * @param string $from_name   From name.
 * @param string $from_email  From email.
 * @param string $subject     Email subject.
 * @param string $content     Email content.
 * @return int Number of emails sent successfully.
 */
function ml_send_bulk_emails( $subscribers, $from_name, $from_email, $subject, $content ) {
	$sent_count = 0;
	$headers    = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_email . '>',
	);

	foreach ( $subscribers as $subscriber ) {
		// Replace variables in content.
		$personalized_content = str_replace(
			array( '{{nome}}', '{{apelido}}', '{{correo}}' ),
			array( $subscriber['name'], $subscriber['surname'], $subscriber['email'] ),
			$content
		);

		// Send email.
		if ( wp_mail( $subscriber['email'], $subject, $personalized_content, $headers ) ) {
			++$sent_count;
		}

		// Small delay to prevent overwhelming the server.
		usleep( 100000 ); // 0.1 seconds
	}

	return $sent_count;
}

/**
 * Log email sending activity.
 *
 * @param int    $list_id      List ID.
 * @param string $subject      Email subject.
 * @param int    $total_count  Total subscribers.
 * @param int    $sent_count   Successfully sent emails.
 */
function ml_log_email_activity( $list_id, $subject, $total_count, $sent_count ) {
	$log_entry = array(
		'date'    => current_time( 'mysql' ),
		'list_id' => $list_id,
		'subject' => $subject,
		'total'   => $total_count,
		'sent'    => $sent_count,
		'user_id' => get_current_user_id(),
	);

	$existing_logs = get_option( 'ml_email_logs', array() );
	$existing_logs[] = $log_entry;

	// Keep only last 100 entries.
	if ( count( $existing_logs ) > 100 ) {
		$existing_logs = array_slice( $existing_logs, -100 );
	}

	update_option( 'ml_email_logs', $existing_logs );
}

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook_suffix The current admin page.
 */
function ml_enqueue_admin_scripts( $hook_suffix ) {
	// Only enqueue on our email sender page.
	if ( 'ml_mailing_lists_page_ml-send-email' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();

	// Ensure TinyMCE is loaded.
	if ( ! class_exists( '_WP_Editors', false ) ) {
		require_once ABSPATH . WPINC . '/class-wp-editor.php';
	}

	_WP_Editors::enqueue_scripts();
}
