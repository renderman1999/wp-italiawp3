<?php
/**
 * Condivisione dataset (WhatsApp, email) in pagina dettaglio.
 *
 * @package ItaliaWP3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Opzione Dettagli: abilita pulsanti condivisione in fondo al dettaglio dataset.
 *
 * @return bool
 */
function italiawp3_is_dataset_sharing_enabled() {
	return get_option( 'dettagli-abilita-condivisione-dataset', '' ) === '1';
}

/**
 * URL condivisione WhatsApp (testo + link pagina).
 *
 * @param string $title Titolo dataset.
 * @param string $url   Permalink dataset.
 * @return string
 */
function italiawp3_dataset_share_whatsapp_url( $title, $url ) {
	$text = trim( $title ) !== '' ? $title . ' - ' . $url : $url;
	return 'https://wa.me/?text=' . rawurlencode( $text );
}

/**
 * URL condivisione email (mailto).
 *
 * @param string $title Titolo dataset.
 * @param string $url   Permalink dataset.
 * @return string
 */
function italiawp3_dataset_share_email_url( $title, $url ) {
	$subject = trim( $title ) !== ''
		? sprintf(
			/* translators: %s: dataset title */
			__( 'Dataset: %s', 'italiawp2' ),
			$title
		)
		: __( 'Dataset open data', 'italiawp2' );

	$body = trim( $title ) !== ''
		? sprintf(
			/* translators: 1: dataset title, 2: dataset URL */
			__( "Ti segnalo questo dataset:\n\n%1\$s\n\n%2\$s", 'italiawp2' ),
			$title,
			$url
		)
		: $url;

	return 'mailto:?subject=' . rawurlencode( $subject ) . '&body=' . rawurlencode( $body );
}

/**
 * Card condivisione in fondo al dettaglio dataset.
 *
 * @param int $post_id ID dataset.
 * @return void
 */
function italiawp3_render_dataset_share_block( $post_id ) {
	if ( ! italiawp3_is_dataset_sharing_enabled() ) {
		return;
	}

	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return;
	}

	$url   = get_permalink( $post_id );
	$title = get_the_title( $post_id );
	if ( ! $url ) {
		return;
	}

	$whatsapp_url = italiawp3_dataset_share_whatsapp_url( $title, $url );
	$email_url    = italiawp3_dataset_share_email_url( $title, $url );
	$icon_base    = get_template_directory_uri();
	?>
	<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card dataset-detail-card--share">
		<div class="card-body">
			<section class="dataset-detail-share condividi" aria-labelledby="dataset-detail-share-heading">
				<h3 id="dataset-detail-share-heading" class="h6 text-uppercase mb-3"><?php esc_html_e( 'Condividi questo dataset', 'italiawp2' ); ?></h3>
				<div class="dataset-detail-share__actions">
					<a
						href="<?php echo esc_url( $whatsapp_url ); ?>"
						class="btn btn-outline-success btn-sm dataset-detail-share__btn dataset-detail-share__btn--whatsapp"
						target="_blank"
						rel="noopener noreferrer"
					>
						<svg class="icon" aria-hidden="true" focusable="false">
							<use xlink:href="<?php echo esc_url( $icon_base ); ?>/static/img/ponmetroca.svg#ca-whatsapp"></use>
						</svg>
						<?php esc_html_e( 'WhatsApp', 'italiawp2' ); ?>
					</a>
					<a
						href="<?php echo esc_url( $email_url ); ?>"
						class="btn btn-outline-primary btn-sm dataset-detail-share__btn dataset-detail-share__btn--email"
					>
						<svg class="icon" aria-hidden="true" focusable="false">
							<use xlink:href="<?php echo esc_url( $icon_base ); ?>/static/img/bootstrap-italia.svg#it-mail"></use>
						</svg>
						<?php esc_html_e( 'Email', 'italiawp2' ); ?>
					</a>
				</div>
			</section>
		</div>
	</div>
	<?php
}
