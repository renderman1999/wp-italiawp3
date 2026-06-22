<?php
/**
 * Notizie con scadenza: alert dismissibili sopra l'header.
 *
 * @package ItaliaWP3
 */

defined( 'ABSPATH' ) || exit;

const ITALIAWP3_NOTICE_POST_TYPE = 'italiawp_notice';
const ITALIAWP3_NOTICE_META_END           = '_italiawp_notice_end';
const ITALIAWP3_NOTICE_META_START         = '_italiawp_notice_start';
const ITALIAWP3_NOTICE_META_DISPLAY_DAYS  = '_italiawp_notice_display_days';
const ITALIAWP3_NOTICE_META_TYPE          = '_italiawp_notice_type';
const ITALIAWP3_NOTICE_STORAGE_KEY        = 'italiawp3_site_notices';

add_action( 'init', 'italiawp3_register_notice_post_type' );
add_action( 'add_meta_boxes', 'italiawp3_notice_meta_boxes' );
add_action( 'save_post_' . ITALIAWP3_NOTICE_POST_TYPE, 'italiawp3_notice_save_meta', 10, 2 );
add_filter( 'manage_' . ITALIAWP3_NOTICE_POST_TYPE . '_posts_columns', 'italiawp3_notice_admin_columns' );
add_action( 'manage_' . ITALIAWP3_NOTICE_POST_TYPE . '_posts_custom_column', 'italiawp3_notice_admin_column_content', 10, 2 );
add_action( 'wp_enqueue_scripts', 'italiawp3_enqueue_notice_assets', 25 );
add_action( 'admin_enqueue_scripts', 'italiawp3_notice_admin_assets' );

/**
 * Registra il CPT Notizie (solo admin, nessuna pagina pubblica).
 */
function italiawp3_register_notice_post_type() {
	$labels = array(
		'name'               => __( 'Notizie', 'italiawp2' ),
		'singular_name'      => __( 'Notizia', 'italiawp2' ),
		'menu_name'          => __( 'Notizie', 'italiawp2' ),
		'add_new'            => __( 'Aggiungi notizia', 'italiawp2' ),
		'add_new_item'       => __( 'Aggiungi notizia', 'italiawp2' ),
		'edit_item'          => __( 'Modifica notizia', 'italiawp2' ),
		'new_item'           => __( 'Nuova notizia', 'italiawp2' ),
		'view_item'          => __( 'Visualizza notizia', 'italiawp2' ),
		'search_items'       => __( 'Cerca notizie', 'italiawp2' ),
		'not_found'          => __( 'Nessuna notizia trovata', 'italiawp2' ),
		'not_found_in_trash' => __( 'Nessuna notizia nel cestino', 'italiawp2' ),
	);

	register_post_type(
		ITALIAWP3_NOTICE_POST_TYPE,
		array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 58,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
		)
	);
}

/**
 * Tipi alert Bootstrap Italia ammessi.
 *
 * @return array<string, string>
 */
function italiawp3_notice_type_choices() {
	return array(
		'info'    => __( 'Informazione', 'italiawp2' ),
		'success' => __( 'Successo', 'italiawp2' ),
		'warning' => __( 'Avviso', 'italiawp2' ),
		'danger'  => __( 'Importante', 'italiawp2' ),
	);
}

/**
 * Normalizza datetime-local in formato Y-m-d H:i:s (timezone sito).
 *
 * @param string $raw Valore da input datetime-local.
 * @return string
 */
function italiawp3_notice_normalize_datetime( $raw ) {
	$raw = trim( (string) $raw );
	if ( $raw === '' ) {
		return '';
	}
	$raw = str_replace( 'T', ' ', $raw );
	if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $raw ) ) {
		$raw .= ':00';
	}
	$ts = strtotime( $raw );
	if ( ! $ts ) {
		return '';
	}
	return wp_date( 'Y-m-d H:i:s', $ts );
}

/**
 * Formato datetime-local per input admin.
 *
 * @param string $stored Valore Y-m-d H:i:s.
 * @return string
 */
function italiawp3_notice_datetime_for_input( $stored ) {
	$stored = trim( (string) $stored );
	if ( $stored === '' ) {
		return '';
	}
	$ts = strtotime( $stored );
	if ( ! $ts ) {
		return '';
	}
	return wp_date( 'Y-m-d\TH:i', $ts );
}

/**
 * Metabox impostazioni notizia.
 */
function italiawp3_notice_meta_boxes() {
	add_meta_box(
		'italiawp_notice_settings',
		__( 'Impostazioni visualizzazione', 'italiawp2' ),
		'italiawp3_notice_settings_metabox',
		ITALIAWP3_NOTICE_POST_TYPE,
		'normal',
		'high'
	);
}

/**
 * @param WP_Post $post Post corrente.
 */
function italiawp3_notice_settings_metabox( $post ) {
	wp_nonce_field( 'italiawp3_notice_save', 'italiawp3_notice_nonce' );

	$end          = get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_END, true );
	$start        = get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_START, true );
	$display_days = (int) get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_DISPLAY_DAYS, true );
	$type         = get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_TYPE, true );
	if ( ! array_key_exists( (string) $type, italiawp3_notice_type_choices() ) ) {
		$type = 'info';
	}
	?>
	<p class="description"><?php esc_html_e( 'La notizia compare come alert sopra l\'header del sito. Dopo la scadenza non viene piu mostrata a nessuno.', 'italiawp2' ); ?></p>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="italiawp_notice_end"><?php esc_html_e( 'Scadenza', 'italiawp2' ); ?></label></th>
			<td>
				<input type="datetime-local" id="italiawp_notice_end" name="italiawp_notice_end" class="regular-text" value="<?php echo esc_attr( italiawp3_notice_datetime_for_input( $end ) ); ?>" required>
				<p class="description"><?php esc_html_e( 'Data e ora in cui la notizia smette di essere visibile per tutti.', 'italiawp2' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="italiawp_notice_start"><?php esc_html_e( 'Inizio (opzionale)', 'italiawp2' ); ?></label></th>
			<td>
				<input type="datetime-local" id="italiawp_notice_start" name="italiawp_notice_start" class="regular-text" value="<?php echo esc_attr( italiawp3_notice_datetime_for_input( $start ) ); ?>">
				<p class="description"><?php esc_html_e( 'Se vuoto, la notizia e attiva dalla pubblicazione.', 'italiawp2' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="italiawp_notice_display_days"><?php esc_html_e( 'Giorni di visualizzazione per utente', 'italiawp2' ); ?></label></th>
			<td>
				<input type="number" id="italiawp_notice_display_days" name="italiawp_notice_display_days" class="small-text" min="0" step="1" value="<?php echo esc_attr( (string) max( 0, $display_days ) ); ?>">
				<p class="description"><?php esc_html_e( 'Giorni dalla prima visita in cui ogni utente puo ancora vedere la notizia (0 = fino a scadenza o chiusura manuale).', 'italiawp2' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="italiawp_notice_type"><?php esc_html_e( 'Tipo alert', 'italiawp2' ); ?></label></th>
			<td>
				<select id="italiawp_notice_type" name="italiawp_notice_type">
					<?php foreach ( italiawp3_notice_type_choices() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<p class="description"><?php esc_html_e( 'Ordine di visualizzazione: usa il campo "Ordine" nella colonna destra (numero piu basso = piu in alto).', 'italiawp2' ); ?></p>
	<?php
}

/**
 * Salva meta notizia.
 *
 * @param int     $post_id ID post.
 * @param WP_Post $post    Post.
 */
function italiawp3_notice_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['italiawp3_notice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['italiawp3_notice_nonce'] ) ), 'italiawp3_notice_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$end = isset( $_POST['italiawp_notice_end'] ) ? italiawp3_notice_normalize_datetime( wp_unslash( $_POST['italiawp_notice_end'] ) ) : '';
	if ( $end === '' ) {
		return;
	}

	$start = isset( $_POST['italiawp_notice_start'] ) ? italiawp3_notice_normalize_datetime( wp_unslash( $_POST['italiawp_notice_start'] ) ) : '';
	$display_days = isset( $_POST['italiawp_notice_display_days'] ) ? max( 0, (int) $_POST['italiawp_notice_display_days'] ) : 0;
	$type = isset( $_POST['italiawp_notice_type'] ) ? sanitize_key( wp_unslash( $_POST['italiawp_notice_type'] ) ) : 'info';
	if ( ! array_key_exists( $type, italiawp3_notice_type_choices() ) ) {
		$type = 'info';
	}

	update_post_meta( $post_id, ITALIAWP3_NOTICE_META_END, $end );
	update_post_meta( $post_id, ITALIAWP3_NOTICE_META_START, $start );
	update_post_meta( $post_id, ITALIAWP3_NOTICE_META_DISPLAY_DAYS, $display_days );
	update_post_meta( $post_id, ITALIAWP3_NOTICE_META_TYPE, $type );
}

/**
 * @param string[] $columns Colonne lista admin.
 * @return string[]
 */
function italiawp3_notice_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['italiawp_notice_end']   = __( 'Scadenza', 'italiawp2' );
			$new['italiawp_notice_type']  = __( 'Tipo', 'italiawp2' );
			$new['italiawp_notice_days']  = __( 'Giorni utente', 'italiawp2' );
		}
	}
	return $new;
}

/**
 * @param string $column  Nome colonna.
 * @param int    $post_id ID post.
 */
function italiawp3_notice_admin_column_content( $column, $post_id ) {
	if ( $column === 'italiawp_notice_end' ) {
		$end = get_post_meta( $post_id, ITALIAWP3_NOTICE_META_END, true );
		echo $end ? esc_html( wp_date( 'd/m/Y H:i', strtotime( (string) $end ) ) ) : '&mdash;';
		return;
	}
	if ( $column === 'italiawp_notice_type' ) {
		$type = get_post_meta( $post_id, ITALIAWP3_NOTICE_META_TYPE, true );
		$choices = italiawp3_notice_type_choices();
		echo esc_html( isset( $choices[ $type ] ) ? $choices[ $type ] : $choices['info'] );
		return;
	}
	if ( $column === 'italiawp_notice_days' ) {
		$days = (int) get_post_meta( $post_id, ITALIAWP3_NOTICE_META_DISPLAY_DAYS, true );
		echo esc_html( $days > 0 ? (string) $days : __( 'Illimitato', 'italiawp2' ) );
	}
}

/**
 * Asset admin CPT Notizie.
 *
 * @param string $hook Hook pagina.
 */
function italiawp3_notice_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== ITALIAWP3_NOTICE_POST_TYPE ) {
		return;
	}
	wp_enqueue_style(
		'italiawp2-admin-css',
		get_template_directory_uri() . '/inc/admin.css',
		array(),
		wp_get_theme()->get( 'Version' )
	);
}

/**
 * Notizie attive per il frontend.
 *
 * @return array<int, array<string, mixed>>
 */
function italiawp3_get_active_notices() {
	$now_mysql = current_time( 'mysql' );

	$query = new WP_Query(
		array(
			'post_type'              => ITALIAWP3_NOTICE_POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => 20,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'meta_query'             => array(
				'relation' => 'AND',
				array(
					'key'     => ITALIAWP3_NOTICE_META_END,
					'value'   => $now_mysql,
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => ITALIAWP3_NOTICE_META_START,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => ITALIAWP3_NOTICE_META_START,
						'value'   => '',
						'compare' => '=',
					),
					array(
						'key'     => ITALIAWP3_NOTICE_META_START,
						'value'   => $now_mysql,
						'compare' => '<=',
						'type'    => 'DATETIME',
					),
				),
			),
		)
	);

	$notices = array();
	foreach ( $query->posts as $post ) {
		$type = get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_TYPE, true );
		if ( ! array_key_exists( (string) $type, italiawp3_notice_type_choices() ) ) {
			$type = 'info';
		}
		$content = apply_filters( 'the_content', $post->post_content );
		if ( trim( wp_strip_all_tags( $content ) ) === '' ) {
			continue;
		}
		$notice = array(
			'id'           => (int) $post->ID,
			'title'        => get_the_title( $post ),
			'content'      => $content,
			'type'         => $type,
			'version'      => md5( $post->post_modified_gmt . '|' . $post->post_content ),
			'displayDays'  => max( 0, (int) get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_DISPLAY_DAYS, true ) ),
			'endTimestamp' => strtotime( (string) get_post_meta( $post->ID, ITALIAWP3_NOTICE_META_END, true ) ),
		);
		if ( ! italiawp3_is_notice_visible_to_visitor( $notice ) ) {
			continue;
		}
		$notices[] = $notice;
	}

	return $notices;
}

/**
 * Chiave store visitatore per una notizia (id + versione contenuto).
 *
 * @param int    $id      ID notizia.
 * @param string $version Hash versione.
 * @return string
 */
function italiawp3_notice_store_key( $id, $version ) {
	return (int) $id . ':' . (string) $version;
}

/**
 * Store lato visitatore (cookie sincronizzato con localStorage via JS).
 *
 * @return array<string, array<string, mixed>>
 */
function italiawp3_read_notice_visitor_store() {
	static $store = null;

	if ( null !== $store ) {
		return $store;
	}

	$store = array();
	if ( empty( $_COOKIE[ ITALIAWP3_NOTICE_STORAGE_KEY ] ) ) {
		return $store;
	}

	$raw = wp_unslash( $_COOKIE[ ITALIAWP3_NOTICE_STORAGE_KEY ] );
	if ( ! is_string( $raw ) || $raw === '' ) {
		return $store;
	}

	$decoded = json_decode( $raw, true );
	if ( is_array( $decoded ) ) {
		$store = $decoded;
	}

	return $store;
}

/**
 * True se la notizia va mostrata a questo visitatore (non dismissata / non scaduta per utente).
 *
 * @param array<string, mixed> $notice Dati notizia da italiawp3_get_active_notices().
 * @return bool
 */
function italiawp3_is_notice_visible_to_visitor( array $notice ) {
	$key   = italiawp3_notice_store_key( $notice['id'], $notice['version'] );
	$store = italiawp3_read_notice_visitor_store();
	$entry = ( isset( $store[ $key ] ) && is_array( $store[ $key ] ) ) ? $store[ $key ] : null;
	$now   = time();

	if ( ! empty( $notice['endTimestamp'] ) && $now > (int) $notice['endTimestamp'] ) {
		return false;
	}

	if ( ! $entry ) {
		return true;
	}

	if ( ! empty( $entry['dismissed'] ) ) {
		return false;
	}

	$display_days = (int) $notice['displayDays'];
	if ( $display_days > 0 && ! empty( $entry['firstSeen'] ) ) {
		$expires_at = (int) $entry['firstSeen'] + ( $display_days * DAY_IN_SECONDS );
		if ( $now >= $expires_at ) {
			return false;
		}
	}

	return true;
}

/**
 * Sincronizza localStorage -> cookie prima del render (evita flash al reload successivo).
 */
function italiawp3_notice_head_sync_script() {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	(function () {
		try {
			var key = <?php echo wp_json_encode( ITALIAWP3_NOTICE_STORAGE_KEY ); ?>;
			var ls = window.localStorage.getItem(key);
			if (!ls || document.cookie.indexOf(key + '=') !== -1) {
				return;
			}
			document.cookie = key + '=' + encodeURIComponent(ls) + '; path=/; max-age=31536000; SameSite=Lax';
		} catch (e) {}
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'italiawp3_notice_head_sync_script', 1 );

/**
 * Enqueue script/CSS notizie frontend.
 */
function italiawp3_enqueue_notice_assets() {
	if ( is_admin() ) {
		return;
	}
	$notices = italiawp3_get_active_notices();
	if ( $notices === array() ) {
		return;
	}

	wp_enqueue_script(
		'italiawp3-site-notices',
		get_template_directory_uri() . '/inc/notices.js',
		array( 'jquery', 'italiawp2-bootstrap-italia' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
	wp_localize_script(
		'italiawp3-site-notices',
		'italiawp3SiteNotices',
		array(
			'storageKey' => ITALIAWP3_NOTICE_STORAGE_KEY,
			'notices'    => $notices,
			'now'        => time(),
		)
	);
}

/**
 * Render alert sopra l'header.
 */
function italiawp3_render_site_notices() {
	if ( is_admin() ) {
		return;
	}

	$notices = italiawp3_get_active_notices();
	if ( $notices === array() ) {
		return;
	}
	?>
	<div id="italiawp-site-notices" class="italiawp-site-notices" aria-live="polite">
		<?php foreach ( $notices as $notice ) : ?>
			<div
				class="alert alert-<?php echo esc_attr( $notice['type'] ); ?> alert-dismissible fade show italiawp-site-notice mb-0"
				role="alert"
				data-notice-id="<?php echo esc_attr( (string) $notice['id'] ); ?>"
				data-notice-version="<?php echo esc_attr( $notice['version'] ); ?>"
				data-display-days="<?php echo esc_attr( (string) $notice['displayDays'] ); ?>"
				data-end-ts="<?php echo esc_attr( (string) $notice['endTimestamp'] ); ?>"
			>
				<div class="container">
					<div class="italiawp-site-notice__inner">
						<?php if ( $notice['title'] !== '' ) : ?>
							<p class="italiawp-site-notice__title mb-0"><strong><?php echo esc_html( $notice['title'] ); ?></strong></p>
						<?php endif; ?>
						<button type="button" class="btn btn-sm btn-outline-primary italiawp-site-notice__read">
							<?php esc_html_e( 'Leggi Notizia', 'italiawp2' ); ?>
						</button>
					</div>
					<button type="button" class="close italiawp-site-notice__close" data-dismiss="alert" aria-label="<?php esc_attr_e( 'Chiudi notizia', 'italiawp2' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Modal lettura notizia (footer).
 */
function italiawp3_render_notice_modal() {
	if ( is_admin() ) {
		return;
	}
	if ( italiawp3_get_active_notices() === array() ) {
		return;
	}
	?>
	<div class="modal fade italiawp3-notice-modal" id="italiawp3-notice-modal" tabindex="-1" role="dialog" aria-labelledby="italiawp3-notice-modal-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title h5 mb-0" id="italiawp3-notice-modal-title"></h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Chiudi', 'italiawp2' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body italiawp3-notice-modal__body"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary italiawp3-notice-modal__done">
						<?php esc_html_e( 'Ho letto', 'italiawp2' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'wp_footer', 'italiawp3_render_notice_modal', 6 );
