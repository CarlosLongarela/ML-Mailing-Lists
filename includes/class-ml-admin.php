<?php
/**
 * Admin functionality for ML Mailing Lists
 *
 * @package ML_Mailing_Lists
 * @subpackage Admin
 * @since 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class ML_Admin
 *
 * Handles the admin functionality for the plugin.
 */
class ML_Admin {

	/**
	 * Unique instance of the class
	 *
	 * @var ML_Admin
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
	 * @return ML_Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize admin functionality
	 */
	private function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_post_ml_send_bulk_email', array( $this, 'handle_bulk_email' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add admin menu pages
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'edit.php?post_type=ml_suscriptor',
			'Envío Masivo de Correos',
			'Envío Masivo',
			'manage_options',
			'ml-bulk-email',
			array( $this, 'bulk_email_page' )
		);

		add_submenu_page(
			'edit.php?post_type=ml_suscriptor',
			'Exportar Suscriptores',
			'Exportar',
			'manage_options',
			'ml-export',
			array( $this, 'export_page' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'ml-bulk-email' ) !== false || strpos( $hook, 'ml-export' ) !== false ) {
			wp_enqueue_style( 'ml-admin-style', ML_PLUGIN_URL . '/assets/css/admin.css', array(), ML_PLUGIN_VERSION );
			wp_enqueue_script( 'ml-admin-script', ML_PLUGIN_URL . '/assets/js/admin.js', array(), ML_PLUGIN_VERSION, true );
		}
	}

	/**
	 * Display bulk email page
	 */
	public function bulk_email_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Non tes permisos suficientes para acceder a esta páxina.' );
		}

		// Get available mailing lists.
		$lists = get_terms(
			array(
				'taxonomy'   => 'ml_lista',
				'hide_empty' => false,
			)
		);

		?>
		<div class="wrap">
			<h1>Envío Masivo de Correos</h1>

			<?php if ( isset( $_GET['sent'] ) && '1' === $_GET['sent'] ) : ?>
				<div class="notice notice-success is-dismissible">
					<p>Correos enviados correctamente.</p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ml-bulk-email-form">
				<?php wp_nonce_field( 'ml_bulk_email_action', 'ml_bulk_email_nonce' ); ?>
				<input type="hidden" name="action" value="ml_send_bulk_email" />

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="ml_mailing_list">Lista de Correo</label>
						</th>
						<td>
							<select name="ml_mailing_list" id="ml_mailing_list" required>
								<option value="">Selecciona unha lista</option>
								<?php foreach ( $lists as $list ) : ?>
									<option value="<?php echo esc_attr( $list->term_id ); ?>">
										<?php echo esc_html( $list->name ); ?> (<?php echo esc_html( $list->count ); ?> suscriptores)
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="ml_email_subject">Asunto</label>
						</th>
						<td>
							<input type="text" name="ml_email_subject" id="ml_email_subject" class="regular-text" required />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="ml_email_content">Contido do Correo</label>
						</th>
						<td>
							<?php
							wp_editor(
								'',
								'ml_email_content',
								array(
									'textarea_name' => 'ml_email_content',
									'media_buttons' => true,
									'textarea_rows' => 10,
									'teeny'         => false,
								)
							);
							?>
							<p class="description">
								Podes usar as seguintes variables no contido:<br />
								<code>{{nome}}</code> - Nome do suscriptor<br />
								<code>{{apelido}}</code> - Apelido do suscriptor<br />
								<code>{{correo}}</code> - Correo do suscriptor
							</p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Enviar Correos" />
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Display export page
	 */
	public function export_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Non tes permisos suficientes para acceder a esta páxina.' );
		}

		// Get available mailing lists.
		$lists = get_terms(
			array(
				'taxonomy'   => 'ml_lista',
				'hide_empty' => false,
			)
		);

		?>
		<div class="wrap">
			<h1>Exportar Suscriptores</h1>

			<div class="ml-export-options">
				<h2>Opcións de Exportación</h2>

				<form method="get" class="ml-export-form">
					<input type="hidden" name="page" value="ml-export" />
					<input type="hidden" name="action" value="export" />
					<?php wp_nonce_field( 'ml_export_action', 'ml_export_nonce' ); ?>

					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="ml_export_list">Lista de Correo</label>
							</th>
							<td>
								<select name="ml_export_list" id="ml_export_list">
									<option value="">Todas as listas</option>
									<?php foreach ( $lists as $list ) : ?>
										<option value="<?php echo esc_attr( $list->term_id ); ?>">
											<?php echo esc_html( $list->name ); ?> (<?php echo esc_html( $list->count ); ?> suscriptores)
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="ml_export_format">Formato</label>
							</th>
							<td>
								<select name="ml_export_format" id="ml_export_format">
									<option value="csv">CSV</option>
									<option value="txt">TXT</option>
								</select>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Exportar" />
					</p>
				</form>
			</div>
		</div>
		<?php

		// Handle export if requested.
		if ( isset( $_GET['action'] ) && 'export' === $_GET['action'] ) {
			$this->handle_export();
		}
	}

	/**
	 * Handle bulk email sending
	 */
	public function handle_bulk_email() {
		// Verify nonce.
		if ( ! wp_verify_nonce( $_POST['ml_bulk_email_nonce'], 'ml_bulk_email_action' ) ) {
			wp_die( 'Erro de seguridade.' );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Non tes permisos suficientes.' );
		}

		$list_id = intval( $_POST['ml_mailing_list'] );
		$subject = sanitize_text_field( $_POST['ml_email_subject'] );
		$content = wp_kses_post( $_POST['ml_email_content'] );

		// Get subscribers from the selected list.
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

		$sent_count = 0;
		foreach ( $subscribers as $subscriber ) {
			$name    = get_post_meta( $subscriber->ID, 'nome', true );
			$surname = get_post_meta( $subscriber->ID, 'apelido', true );
			$email   = get_post_meta( $subscriber->ID, 'correo', true );

			// Replace template variables.
			$personalized_content = str_replace(
				array( '{{nome}}', '{{apelido}}', '{{correo}}' ),
				array( $name, $surname, $email ),
				$content
			);

			// Send email.
			$sent = wp_mail(
				$email,
				$subject,
				$personalized_content,
				array( 'Content-Type: text/html; charset=UTF-8' )
			);

			if ( $sent ) {
				++$sent_count;
			}
		}

		// Log the bulk email.
		$this->log_bulk_email( $list_id, $subject, $sent_count );

		// Redirect with success message.
		wp_safe_redirect( add_query_arg( 'sent', '1', wp_get_referer() ) );
		exit;
	}

	/**
	 * Handle export requests
	 */
	private function handle_export() {
		// Verify nonce.
		if ( ! wp_verify_nonce( $_GET['ml_export_nonce'], 'ml_export_action' ) ) {
			wp_die( 'Erro de seguridade.' );
		}

		$list_id = ! empty( $_GET['ml_export_list'] ) ? intval( $_GET['ml_export_list'] ) : 0;
		$format  = sanitize_text_field( $_GET['ml_export_format'] );

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

		// Generate filename.
		$list_name = $list_id > 0 ? get_term( $list_id, 'ml_lista' )->name : 'todas-listas';
		$filename  = 'suscriptores-' . sanitize_file_name( $list_name ) . '-' . date( 'Y-m-d' ) . '.' . $format;

		// Set headers for file download.
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		if ( 'csv' === $format ) {
			$this->export_csv( $subscribers );
		} else {
			$this->export_txt( $subscribers );
		}

		exit;
	}

	/**
	 * Export subscribers as CSV
	 *
	 * @param array $subscribers Array of subscriber posts.
	 */
	private function export_csv( $subscribers ) {
		// Output CSV headers.
		echo "Nome,Apelido,Correo,Data Subscrición,Lista\n";

		foreach ( $subscribers as $subscriber ) {
			$name    = get_post_meta( $subscriber->ID, 'nome', true );
			$surname = get_post_meta( $subscriber->ID, 'apelido', true );
			$email   = get_post_meta( $subscriber->ID, 'correo', true );
			$date    = get_post_meta( $subscriber->ID, 'data_subscripcion', true );

			// Get list names.
			$lists      = get_the_terms( $subscriber->ID, 'ml_lista' );
			$list_names = $lists ? implode( '; ', wp_list_pluck( $lists, 'name' ) ) : '';

			// Output CSV row.
			printf(
				'"%s","%s","%s","%s","%s"' . "\n",
				esc_attr( $name ),
				esc_attr( $surname ),
				esc_attr( $email ),
				esc_attr( $date ),
				esc_attr( $list_names )
			);
		}
	}

	/**
	 * Export subscribers as TXT
	 *
	 * @param array $subscribers Array of subscriber posts.
	 */
	private function export_txt( $subscribers ) {
		foreach ( $subscribers as $subscriber ) {
			$name    = get_post_meta( $subscriber->ID, 'nome', true );
			$surname = get_post_meta( $subscriber->ID, 'apelido', true );
			$email   = get_post_meta( $subscriber->ID, 'correo', true );

			echo esc_attr( $name ) . ' ' . esc_attr( $surname ) . ' <' . esc_attr( $email ) . ">\n";
		}
	}

	/**
	 * Log bulk email sending
	 *
	 * @param int    $list_id    The mailing list ID.
	 * @param string $subject    The email subject.
	 * @param int    $sent_count Number of emails sent.
	 */
	private function log_bulk_email( $list_id, $subject, $sent_count ) {
		$log_entry = array(
			'date'       => current_time( 'mysql' ),
			'list_id'    => $list_id,
			'subject'    => $subject,
			'sent_count' => $sent_count,
			'user_id'    => get_current_user_id(),
		);

		$logs   = get_option( 'ml_bulk_email_logs', array() );
		$logs[] = $log_entry;

		// Keep only the last 100 log entries.
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}

		update_option( 'ml_bulk_email_logs', $logs );
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
