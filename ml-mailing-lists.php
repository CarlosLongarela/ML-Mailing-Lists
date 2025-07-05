<?php
/**
 * Plugin Name: ML Mailing Lists
 * Description: Plugin para gestionar listas de correo y suscripciones.
 * Version: 1.0
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
	<div class="<?php echo esc_attr( $atts['clase_css'] ); ?>">
		<?php if ( ! empty( $message ) ) : ?>
			<div class="ml-message"><?php echo $message; ?></div>
		<?php endif; ?>

		<form id="<?php echo esc_attr( $form_id ); ?>" method="post" action="">
			<h3><?php echo esc_html( $atts['titulo'] ); ?></h3>

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
		</form>
	</div>

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

	.<?php echo esc_attr( $atts['css_class'] ); ?> {
		max-width: 500px;
		margin: 20px 0;
		padding: 20px;
		border: 1px solid var(--ml-border-color);
		border-radius: 5px;
		background-color: var(--ml-background-color);
	}

	.<?php echo esc_attr( $atts['css_class'] ); ?> h3 {
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
	}

	.ml-submit-btn:hover {
		background-color: var(--ml-secondary-color);
	}

	.ml-mensaje {
		padding: 10px;
		margin-bottom: 15px;
		border-radius: 4px;
	}

	.ml-mensaje.success {
		background-color: var(--ml-success-bg);
		color: var(--ml-success-text);
		border: 1px solid var(--ml-success-bg);
	}

	.ml-mensaje.error {
		background-color: var(--ml-error-bg);
		color: var(--ml-error-text);
		border: 1px solid var(--ml-error-bg);
	}
	</style>
	<?php

	return ob_get_clean();
}

/**
 * Process the subscription form submission.
 */
function ml_process_subscription_form() {
	// Verify nonce for security.
	if ( ! isset( $_POST['ml_nonce'] ) || ! wp_verify_nonce( $_POST['ml_nonce'], 'ml_subscription_' . $_POST['ml_lista_id'] ) ) {
		return '<div class="ml-mensaje error">Error de seguridad. Por favor, inténtelo de nuevo.</div>';
	}

	// Sanitize input data.
	$name    = sanitize_text_field( $_POST['ml_name'] );
	$surname = sanitize_text_field( $_POST['ml_surname'] );
	$email   = sanitize_email( $_POST['ml_mail'] );
	$list_id = intval( $_POST['ml_list_id'] );

	// Validation checks.
	if ( empty( $name ) || empty( $surname ) || empty( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, complete todos los campos obligatorios.</div>';
	}

	if ( ! is_email( $email ) ) {
		return '<div class="ml-mensaje error">Por favor, introduzca un email válido.</div>';
	}

	// Check if email already exists.
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
			'posts_per_page' => 1,
		)
	);

	if ( ! empty( $existing_posts ) ) {
		return '<div class="ml-mensaje error">Este email ya está suscrito.</div>';
	}

	// Create the post for the subscription.
	$post_data = array(
		'post_type'   => 'ml_mailing_lists',
		'post_title'  => $name . ' ' . $surname,
		'post_status' => 'publish',
		'meta_input'  => array(
			'ml_name'    => $name,
			'ml_surname' => $surname,
			'ml_email'   => $email,
		),
	);

	$post_id = wp_insert_post( $post_data );

	if ( is_wp_error( $post_id ) ) {
		return '<div class="ml-mensaje error">Error al procesar la suscripción. Por favor, inténtelo de nuevo.</div>';
	}

	// Assign taxonomy term to the post.
	wp_set_post_terms( $post_id, array( $list_id ), 'ml_lista' );

	// Clear POST variables to prevent resubmission.
	unset( $_POST['ml_nome'], $_POST['ml_apelidos'], $_POST['ml_correo'] );

	return '<div class="ml-mensaje success">¡Gracias! Su suscripción se ha procesado correctamente.</div>';
}
// Register the shortcode.
add_shortcode( 'ml_subscription_form', 'ml_subscription_form_shortcode' );
