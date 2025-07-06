<?php
/**
 * Shortcode class for ML Mailing Lists
 *
 * @package ML Mailing Lists
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ML_Shortcode class
 */
class ML_Shortcode {

	/**
	 * Unique instance of the class
	 *
	 * @var ML_Shortcode
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
	 * @return ML_Shortcode
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize shortcodes
	 */
	private function init() {
		add_shortcode( 'ml_subscription_form', array( $this, 'subscription_form_shortcode' ) );
	}

	/**
	 * Shortcode to display subscription form
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Subscription form HTML.
	 */
	public function subscription_form_shortcode( $atts ) {
		// Shortcode attributes with default values.
		$atts = shortcode_atts(
			array(
				'list_id'   => '',
				'title'     => 'Subscríbete á nosa lista',
				'btn_text'  => 'Subscribirse',
				'css_class' => 'ml-subscription-form',
			),
			$atts
		);

		// Verify that the list ID is provided.
		if ( empty( $atts['list_id'] ) ) {
			return '<p style="color: red;">Erro: Debe especificar o ID da lista.</p>';
		}

		// Verify that the taxonomy 'ml_lista' exists.
		$term = get_term( $atts['list_id'], 'ml_lista' );

		if ( is_wp_error( $term ) || ! $term ) {
			return '<p style="color: red;">Erro: A lista especificada non existe.</p>';
		}

		// Generate unique ID for the form.
		$form_id = 'ml-form-' . $atts['list_id'];

		// Process the form if it has been submitted.
		$message = '';
		if ( isset( $_POST['ml_submit'] ) && $atts['list_id'] === $_POST['ml_list_id'] ) {
			$message = $this->process_subscription_form();
		}

		// Generate nonce for security.
		$nonce = ML_Security::create_nonce( 'ml_subscription_' . $atts['list_id'] );

		// Form HTML.
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
		echo $this->get_subscription_form_css( $atts['css_class'] );

		return ob_get_clean();
	}

	/**
	 * Process subscription form submission
	 *
	 * @return string Result message.
	 */
	private function process_subscription_form() {
		// Verify that required POST variables exist.
		if ( ! isset( $_POST['ml_nonce'], $_POST['ml_list_id'], $_POST['ml_name'], $_POST['ml_surname'], $_POST['ml_mail'] ) ) {
			return '<div class="ml-mensaje error">Datos do formulario incompletos.</div>';
		}

		// Verify nonce for security.
		$list_id_post = intval( $_POST['ml_list_id'] );
		$nonce        = ML_Security::sanitize_text_input( $_POST['ml_nonce'] );

		if ( ! ML_Security::verify_nonce( $nonce, 'ml_subscription_' . $list_id_post ) ) {
			return '<div class="ml-mensaje error">Erro de seguridade. Por favor, inténteo de novo.</div>';
		}

		// Verify honeypot.
		$honeypot = isset( $_POST['ml_honeypot'] ) ? $_POST['ml_honeypot'] : '';
		if ( ML_Security::is_honeypot_triggered( $honeypot ) ) {
			return '<div class="ml-mensaje error">Detección de spam. Solicitude rexeitada.</div>';
		}

		// Get user IP.
		$user_ip = ML_Security::get_user_ip();

		// Verify rate limit.
		if ( ! ML_Security::check_rate_limit( $user_ip ) ) {
			return '<div class="ml-mensaje error">Demasiados intentos. Por favor, agarde unha hora antes de volver intentalo.</div>';
		}

		// Sanitize input data.
		$name    = ML_Security::sanitize_text_input( $_POST['ml_name'] );
		$surname = ML_Security::sanitize_text_input( $_POST['ml_surname'] );
		$email   = ML_Security::sanitize_email_input( $_POST['ml_mail'] );
		$list_id = $list_id_post;

		// Validations.
		$validation_data = array(
			'name'    => $name,
			'surname' => $surname,
			'email'   => $email,
		);

		$validation_result = ML_Security::validate_subscription_data( $validation_data );
		if ( ! $validation_result['valid'] ) {
			return '<div class="ml-mensaje error">' . esc_html( $validation_result['message'] ) . '</div>';
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

		// Update rate limit counter.
		ML_Security::increment_rate_limit( $user_ip );

		// Fire action hook for extensibility.
		do_action( 'ml_subscription_created', $post_id, $email, $list_id );

		// Clear POST variables to prevent resubmission.
		unset( $_POST['ml_name'], $_POST['ml_surname'], $_POST['ml_mail'] );

		return '<div class="ml-mensaje success">¡Grazas! A súa subscrición procesouse correctamente.</div>';
	}

	/**
	 * Get CSS for the subscription form
	 *
	 * @param string $css_class The CSS class of the form.
	 * @return string CSS styles.
	 */
	private function get_subscription_form_css( $css_class ) {
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
	 * Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization
	 */
	private function __wakeup() {}
}
