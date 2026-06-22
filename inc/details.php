<?php
/**
 * Pagina admin "Dettagli" — impostazioni ente, contatti, mappe, Google Analytics.
 *
 * @package ItaliaWP2
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'italiawp2_add_custom_interface' );

/**
 * Registra la pagina Dettagli nel menu admin.
 */
function italiawp2_add_custom_interface() {
	add_menu_page(
		__( 'Dettagli ente', 'italiawp2' ),
		__( 'Dettagli', 'italiawp2' ),
		'manage_options',
		'functions',
		'italiawp2_edit_custom_settings',
		'dashicons-building',
		59
	);
}

add_action( 'admin_enqueue_scripts', 'italiawp2_details_admin_assets' );

/**
 * Bootstrap Italia e stili pagina Dettagli (come Open Data PA).
 *
 * @param string $hook Hook pagina admin.
 */
function italiawp2_details_admin_assets( $hook ) {
	if ( $hook !== 'toplevel_page_functions' ) {
		return;
	}

	wp_enqueue_style(
		'italiawp3-bootstrap-italia-admin',
		'https://cdn.jsdelivr.net/npm/bootstrap-italia@2/dist/css/bootstrap-italia.min.css',
		array(),
		'2.17.3'
	);
	wp_enqueue_style(
		'italiawp2-admin-css',
		get_template_directory_uri() . '/inc/admin.css',
		array( 'italiawp3-bootstrap-italia-admin' ),
		wp_get_theme()->get( 'Version' )
	);
}

add_action( 'admin_init', 'italiawp2_register_details_settings' );
add_action( 'admin_init', 'italiawp2_details_handle_save' );

/**
 * Salva le impostazioni Dettagli (form custom, senza options.php legacy).
 */
function italiawp2_details_handle_save() {
	if ( ! isset( $_POST['italiawp2_details_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permessi insufficienti.', 'italiawp2' ) );
	}

	check_admin_referer( 'italiawp2_details_save', 'italiawp2_details_nonce' );

	foreach ( italiawp2_details_option_names() as $option_name ) {
		if ( italiawp2_details_option_is_checkbox( $option_name ) ) {
			$raw = ( isset( $_POST[ $option_name ] ) && (string) wp_unslash( $_POST[ $option_name ] ) === '1' ) ? '1' : '';
			update_option( $option_name, italiawp2_sanitize_details_option( $raw, $option_name ) );
			continue;
		}

		if ( ! isset( $_POST[ $option_name ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $option_name ] );
		update_option( $option_name, italiawp2_sanitize_details_option( $raw, $option_name ) );
	}

	wp_safe_redirect(
		add_query_arg(
			'settings-updated',
			'true',
			admin_url( 'admin.php?page=functions' )
		)
	);
	exit;
}

/**
 * Nomi opzioni gestite dalla pagina Dettagli.
 *
 * @return string[]
 */
function italiawp2_details_option_names() {
	return array(
		'dettagli-num-articoli',
		'custom-meta-keywords',
		'custom-meta-description',
		'dettagli-link-accessibilita',
		'dettagli-nome-ammin-afferente',
		'dettagli-logo-ammin-afferente',
		'dettagli-url-ammin-afferente',
		'dettagli-id-privacy',
		'dettagli-id-cookie',
		'dettagli-id-notelegali',
		'dettagli-id-contatti',
		'dettagli-indirizzo',
		'dettagli-cap',
		'dettagli-citta',
		'dettagli-telefono',
		'dettagli-fax',
		'dettagli-email',
		'dettagli-email2',
		'dettagli-pec',
		'dettagli-cfpiva',
		'dettagli-codunivoco',
		'dettagli-iban',
		'dettagli-facebook',
		'dettagli-twitter',
		'dettagli-youtube',
		'dettagli-instagram',
		'dettagli-telegram',
		'dettagli-whatsapp',
		'dettagli-linkedin',
		'dettagli-map',
		'dettagli-map-latitude',
		'dettagli-map-longitude',
		'dettagli-map-popup',
		'dettagli-url-accedi',
		'dettagli-ga-measurement-id',
		'dettagli-abilita-condivisione-dataset',
	);
}

/**
 * Opzioni Dettagli salvate come checkbox (off se assenti nel POST).
 *
 * @param string $option_name Nome opzione.
 * @return bool
 */
function italiawp2_details_option_is_checkbox( $option_name ) {
	return in_array( $option_name, array( 'dettagli-abilita-condivisione-dataset' ), true );
}

/**
 * Registra le impostazioni Dettagli con l'API Settings di WordPress.
 */
function italiawp2_register_details_settings() {
	$group = 'italiawp2_details';

	foreach ( italiawp2_details_option_names() as $option_name ) {
		register_setting(
			$group,
			$option_name,
			array(
				'type'              => 'string',
				'sanitize_callback' => italiawp2_details_sanitize_callback( $option_name ),
				'default'           => '',
			)
		);
	}
}

/**
 * Factory callback sanitizzazione per singola opzione.
 *
 * @param string $option_name Nome opzione.
 * @return callable
 */
function italiawp2_details_sanitize_callback( $option_name ) {
	return static function ( $value ) use ( $option_name ) {
		return italiawp2_sanitize_details_option( $value, $option_name );
	};
}

/**
 * Sanitizza un valore in base al nome opzione Dettagli.
 *
 * @param mixed  $value       Valore inviato dal form.
 * @param string $option_name Nome opzione (passato da register_setting).
 * @return string
 */
function italiawp2_sanitize_details_option( $value, $option_name = '' ) {
	if ( ! is_string( $option_name ) || $option_name === '' ) {
		$option_name = '';
	}

	$value = is_string( $value ) ? $value : '';

	switch ( $option_name ) {
		case 'dettagli-num-articoli':
			$n = absint( $value );
			return $n > 0 ? (string) $n : '';

		case 'dettagli-id-privacy':
		case 'dettagli-id-cookie':
		case 'dettagli-id-notelegali':
		case 'dettagli-id-contatti':
			$page_id = absint( $value );
			return $page_id > 0 ? (string) $page_id : '';

		case 'dettagli-link-accessibilita':
		case 'dettagli-logo-ammin-afferente':
		case 'dettagli-url-ammin-afferente':
		case 'dettagli-url-accedi':
		case 'dettagli-facebook':
		case 'dettagli-twitter':
		case 'dettagli-youtube':
		case 'dettagli-instagram':
		case 'dettagli-linkedin':
		case 'dettagli-map':
			return esc_url_raw( $value );

		case 'dettagli-email':
		case 'dettagli-email2':
		case 'dettagli-pec':
			return sanitize_email( $value );

		case 'dettagli-map-popup':
			return wp_kses_post( $value );

		case 'dettagli-ga-measurement-id':
			return italiawp2_sanitize_ga_measurement_id( $value );

		case 'dettagli-abilita-condivisione-dataset':
			return $value === '1' ? '1' : '';

		default:
			return sanitize_text_field( $value );
	}
}

/**
 * @param mixed $value Valore in ingresso.
 * @return string
 */
function italiawp2_sanitize_ga_measurement_id( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( $value === '' ) {
		return '';
	}
	if ( preg_match( '/^G-[A-Z0-9]+$/i', $value ) ) {
		return strtoupper( $value );
	}
	set_transient( 'italiawp2_ga_invalid_notice', 1, 30 );
	return get_option( 'dettagli-ga-measurement-id', '' );
}

add_action( 'wp_head', 'italiawp2_output_google_analytics', 20 );

/**
 * Inserisce gtag GA4 se configurato in Dettagli.
 */
function italiawp2_output_google_analytics() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	$measurement_id = get_option( 'dettagli-ga-measurement-id', '' );
	if ( ! is_string( $measurement_id ) || $measurement_id === '' ) {
		return;
	}
	if ( ! preg_match( '/^G-[A-Z0-9]+$/', $measurement_id ) ) {
		return;
	}

	$measurement_id = esc_attr( $measurement_id );
	?>
	<!-- Google tag (gtag.js) - ItaliaWP3 Dettagli -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $measurement_id; ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo $measurement_id; ?>');
	</script>
	<?php
}

/**
 * Elenco pagine per le select della pagina Dettagli.
 *
 * @return WP_Post[]
 */
function italiawp2_details_get_pages_for_select() {
	static $pages = null;

	if ( $pages !== null ) {
		return $pages;
	}

	$pages = get_pages(
		array(
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
			'post_status' => array( 'publish', 'private' ),
		)
	);

	return is_array( $pages ) ? $pages : array();
}

/**
 * Select pagina (sempre renderizzata, anche senza pagine pubblicate).
 *
 * @param string $name     Nome campo.
 * @param string $selected ID pagina selezionata.
 */
function italiawp2_details_render_page_select( $name, $selected ) {
	$pages       = italiawp2_details_get_pages_for_select();
	$selected    = absint( $selected );
	$page_ids    = wp_list_pluck( $pages, 'ID' );
	$orphan_page = null;

	if ( $selected > 0 && ! in_array( $selected, $page_ids, true ) ) {
		$post = get_post( $selected );
		if ( $post instanceof WP_Post && $post->post_type === 'page' ) {
			$orphan_page = $post;
		}
	}
	?>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="details-page-select">
		<option value="0"><?php esc_html_e( '— Seleziona pagina —', 'italiawp2' ); ?></option>
		<?php if ( $orphan_page ) : ?>
			<option value="<?php echo esc_attr( (string) $orphan_page->ID ); ?>" selected="selected">
				<?php echo esc_html( $orphan_page->post_title . ' (' . $orphan_page->post_status . ')' ); ?>
			</option>
		<?php endif; ?>
		<?php foreach ( $pages as $page ) : ?>
			<option value="<?php echo esc_attr( (string) $page->ID ); ?>" <?php selected( $selected, $page->ID ); ?>>
				<?php echo esc_html( $page->post_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php if ( empty( $pages ) && ! $orphan_page ) : ?>
		<div class="form-text"><?php esc_html_e( 'Nessuna pagina disponibile. Crea una pagina in Pagine.', 'italiawp2' ); ?></div>
	<?php endif; ?>
	<?php
}

/**
 * @param array<string, mixed> $field Definizione campo.
 */
function italiawp2_details_render_field( $field ) {
	$name        = $field['name'];
	$label       = $field['label'];
	$type        = isset( $field['type'] ) ? $field['type'] : 'text';
	$help        = isset( $field['help'] ) ? $field['help'] : '';
	$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$full        = ! empty( $field['full'] );
	$value       = get_option( $name, '' );
	$value       = is_scalar( $value ) ? (string) $value : '';
	$field_class = 'details-field' . ( $full ? ' details-field--full' : '' );
	?>
	<div class="<?php echo esc_attr( $field_class ); ?>">
		<label class="form-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<?php if ( $type === 'page_select' ) : ?>
			<?php italiawp2_details_render_page_select( $name, $value ); ?>
		<?php else : ?>
			<?php
			$input_type = ( $type === 'email' ) ? 'email' : ( ( $type === 'url' ) ? 'url' : 'text' );
			?>
			<input
				type="<?php echo esc_attr( $input_type ); ?>"
				class="form-control"
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo esc_attr( $name ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				<?php echo $placeholder !== '' ? 'placeholder="' . esc_attr( $placeholder ) . '"' : ''; ?>
			/>
		<?php endif; ?>
		<?php if ( $help !== '' ) : ?>
			<div class="form-text"><?php echo esc_html( $help ); ?></div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Apre griglia campi a larghezza piena con accapo automatico.
 */
function italiawp2_details_fields_grid_open() {
	echo '<div class="details-fields-grid">';
}

/**
 * Chiude griglia campi.
 */
function italiawp2_details_fields_grid_close() {
	echo '</div>';
}

/**
 * Pagina impostazioni Dettagli.
 */
function italiawp2_edit_custom_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$ga_id = get_option( 'dettagli-ga-measurement-id', '' );
	?>
	<div class="wrap italiawp3-details-wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Dettagli ente', 'italiawp2' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Informazioni istituzionali, contatti, pagine legali, mappe e tracciamento analytics.', 'italiawp2' ); ?></p>

		<?php if ( get_transient( 'italiawp2_ga_invalid_notice' ) ) : ?>
			<?php delete_transient( 'italiawp2_ga_invalid_notice' ); ?>
			<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Measurement ID Google Analytics non valido. Usa il formato GA4, es. G-XXXXXXXXXX.', 'italiawp2' ); ?></p></div>
		<?php endif; ?>

		<?php if ( isset( $_GET['settings-updated'] ) && sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) === 'true' ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impostazioni salvate.', 'italiawp2' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=functions' ) ); ?>" class="mt-3">
			<?php wp_nonce_field( 'italiawp2_details_save', 'italiawp2_details_nonce' ); ?>
			<input type="hidden" name="italiawp2_details_save" value="1" />

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'SEO e home', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<?php
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-link-accessibilita',
							'label' => __( 'Link dichiarazione accessibilita AGID (URL)', 'italiawp2' ),
							'type'  => 'url',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'custom-meta-keywords',
							'label' => __( 'Meta keywords', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'custom-meta-description',
							'label' => __( 'Meta description (personalizzata)', 'italiawp2' ),
							'full'  => true,
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-num-articoli',
							'label' => __( 'Numero ultimi articoli in home', 'italiawp2' ),
							'help'  => __( 'Consigliato multiplo di 3.', 'italiawp2' ),
						)
					);
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Amministrazione afferente', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<?php
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-nome-ammin-afferente',
							'label' => __( 'Nome amministrazione afferente', 'italiawp2' ),
							'help'  => __( 'Compare se manca il logo o il link.', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-logo-ammin-afferente',
							'label' => __( 'URL logo amministrazione afferente', 'italiawp2' ),
							'type'  => 'url',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-url-ammin-afferente',
							'label' => __( 'URL amministrazione afferente', 'italiawp2' ),
							'type'  => 'url',
						)
					);
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Pagine istituzionali', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<?php
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-id-privacy',
							'label' => __( 'Pagina Privacy', 'italiawp2' ),
							'type'  => 'page_select',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-id-cookie',
							'label' => __( 'Pagina Cookie policy', 'italiawp2' ),
							'type'  => 'page_select',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-id-notelegali',
							'label' => __( 'Pagina Note legali', 'italiawp2' ),
							'type'  => 'page_select',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-id-contatti',
							'label' => __( 'Pagina Contatti', 'italiawp2' ),
							'type'  => 'page_select',
						)
					);
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Header e contatti', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<?php
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-url-accedi',
							'label' => __( 'URL bottone Accedi in header', 'italiawp2' ),
							'type'  => 'url',
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-indirizzo',
							'label' => __( 'Indirizzo', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field( array( 'name' => 'dettagli-cap', 'label' => __( 'CAP', 'italiawp2' ) ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-citta', 'label' => __( 'Citta', 'italiawp2' ) ) );
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-telefono',
							'label' => __( 'Telefono', 'italiawp2' ),
							'help'  => __( 'Senza prefisso +39 e senza punteggiatura.', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-fax',
							'label' => __( 'Fax', 'italiawp2' ),
							'help'  => __( 'Senza prefisso +39 e senza punteggiatura.', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field( array( 'name' => 'dettagli-email', 'label' => __( 'Email', 'italiawp2' ), 'type' => 'email' ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-email2', 'label' => __( 'Email 2 (opzionale)', 'italiawp2' ), 'type' => 'email' ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-pec', 'label' => __( 'PEC', 'italiawp2' ), 'type' => 'email' ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-cfpiva', 'label' => __( 'C.F. / P.IVA', 'italiawp2' ) ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-codunivoco', 'label' => __( 'Codice univoco', 'italiawp2' ) ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-iban', 'label' => __( 'IBAN', 'italiawp2' ) ) );
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Social', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<?php
					foreach ( array(
						'dettagli-facebook'  => 'Facebook',
						'dettagli-twitter'   => 'Twitter / X',
						'dettagli-youtube'   => 'YouTube',
						'dettagli-instagram' => 'Instagram',
						'dettagli-linkedin'  => 'LinkedIn',
					) as $opt => $lbl ) {
						italiawp2_details_render_field(
							array(
								'name'  => $opt,
								'label' => $lbl,
								'type'  => 'url',
							)
						);
					}
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-telegram',
							'label' => __( 'Telegram (solo nome utente)', 'italiawp2' ),
							'help'  => __( 'Senza https://telegram.me/', 'italiawp2' ),
						)
					);
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-whatsapp',
							'label' => __( 'WhatsApp', 'italiawp2' ),
							'help'  => __( 'Numero senza +39 e senza punteggiatura.', 'italiawp2' ),
						)
					);
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Google Analytics (GA4)', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<p class="details-grid-note form-text"><?php esc_html_e( 'Inserisci il Measurement ID del flusso dati GA4. Lo script gtag.js verra aggiunto automaticamente nel frontend del sito.', 'italiawp2' ); ?></p>
					<?php
					italiawp2_details_render_field(
						array(
							'name'        => 'dettagli-ga-measurement-id',
							'label'       => __( 'Measurement ID GA4', 'italiawp2' ),
							'placeholder' => 'G-XXXXXXXXXX',
							'help'        => __( 'Lo trovi in Google Analytics: Admin > Flussi di dati > Web. Lascia vuoto per disattivare.', 'italiawp2' ),
						)
					);
					if ( is_string( $ga_id ) && $ga_id !== '' ) :
						?>
						<div class="details-field details-field--full">
							<p class="form-label mb-1"><?php esc_html_e( 'Anteprima tag attivo', 'italiawp2' ); ?></p>
							<pre class="ga-preview" aria-hidden="true">&lt;script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html( $ga_id ); ?>"&gt;&lt;/script&gt;
gtag('config', '<?php echo esc_html( $ga_id ); ?>');</pre>
						</div>
					<?php endif; ?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Open Data', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<div class="form-check mb-0">
						<input
							type="checkbox"
							class="form-check-input"
							name="dettagli-abilita-condivisione-dataset"
							id="dettagli-abilita-condivisione-dataset"
							value="1"
							<?php checked( get_option( 'dettagli-abilita-condivisione-dataset', '' ), '1' ); ?>
						/>
						<label class="form-check-label" for="dettagli-abilita-condivisione-dataset">
							<?php esc_html_e( 'Abilita condivisione dataset', 'italiawp2' ); ?>
						</label>
					</div>
					<p class="form-text mb-0 mt-2">
						<?php esc_html_e( 'Mostra in fondo alla pagina di ogni dataset i pulsanti per condividere via WhatsApp ed email.', 'italiawp2' ); ?>
					</p>
				</div>
			</div>

			<div class="card card-wrapper mb-4">
				<div class="card-header"><h2><?php esc_html_e( 'Mappa', 'italiawp2' ); ?></h2></div>
				<div class="card-body">
					<?php italiawp2_details_fields_grid_open(); ?>
					<ul class="details-help">
						<li><?php esc_html_e( 'Google Maps: compila il campo iframe SRC.', 'italiawp2' ); ?></li>
						<li><?php esc_html_e( 'OpenStreetMap: compila latitudine e longitudine lasciando vuoto il campo Google.', 'italiawp2' ); ?></li>
					</ul>
					<?php
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-map',
							'label' => __( 'Google Maps iframe SRC (URL)', 'italiawp2' ),
							'type'  => 'url',
							'full'  => true,
						)
					);
					italiawp2_details_render_field( array( 'name' => 'dettagli-map-latitude', 'label' => __( 'Latitudine', 'italiawp2' ) ) );
					italiawp2_details_render_field( array( 'name' => 'dettagli-map-longitude', 'label' => __( 'Longitudine', 'italiawp2' ) ) );
					italiawp2_details_render_field(
						array(
							'name'  => 'dettagli-map-popup',
							'label' => __( 'Testo popup marker (HTML consentito)', 'italiawp2' ),
							'full'  => true,
						)
					);
					?>
					<?php italiawp2_details_fields_grid_close(); ?>
				</div>
			</div>

			<div class="details-actions">
				<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Salva impostazioni', 'italiawp2' ); ?></button>
			</div>
		</form>
	</div>
	<?php
}
