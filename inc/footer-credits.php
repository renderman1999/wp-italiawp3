<?php
/**
 * Footer credits: trigger e modal Bootstrap Italia 4.
 *
 * @package ItaliaWP2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Script modal crediti footer.
 */
function italiawp3_enqueue_footer_credits_assets() {
	wp_enqueue_script(
		'italiawp3-footer-credits',
		get_template_directory_uri() . '/inc/footer-credits.js',
		array( 'jquery', 'italiawp2-bootstrap-italia' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'italiawp3_enqueue_footer_credits_assets', 30 );

/**
 * Markup modal crediti (footer).
 */
function italiawp3_render_footer_credits_modal() {
	$main_theme = wp_get_theme( get_template() );
	$theme_name = $main_theme->get( 'Name' );
	$theme_uri  = $main_theme->get( 'ThemeURI' );
	if ( ! $theme_uri ) {
		$theme_uri = 'https://github.com/renderman1999/wp-italiawp3';
	}
	?>
	<div class="modal fade italiawp3-footer-credits-modal" id="italiawp3-footer-credits-modal" tabindex="-1" role="dialog" aria-labelledby="italiawp3-footer-credits-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h2 class="modal-title h5" id="italiawp3-footer-credits-title"><?php esc_html_e( 'Credits', 'italiawp2' ); ?></h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Chiudi', 'italiawp2' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<ul class="italiawp3-footer-credits-list">
						<li>
							<?php esc_html_e( 'Realizzato con', 'italiawp2' ); ?>
							<a target="_blank" rel="noopener noreferrer" href="https://it.wordpress.org/">WordPress</a>
						</li>
						<li>
							<?php esc_html_e( 'Tema grafico', 'italiawp2' ); ?>
							<a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( $theme_uri ); ?>"><?php echo esc_html( $theme_name ); ?></a>
						</li>
						<li>
							<?php esc_html_e( 'Derivato da', 'italiawp2' ); ?>
							<a target="_blank" rel="noopener noreferrer" href="https://italiawp.borisamico.it/v2/">ItaliaWP2</a>
							(Boris Amico)
						</li>
						<li>
							<?php esc_html_e( 'Personalizzazioni Open Data', 'italiawp2' ); ?> Asymmetrica
						</li>
						<li>
							<?php esc_html_e( 'Basato sul', 'italiawp2' ); ?>
							<a target="_blank" rel="noopener noreferrer" href="https://italia.github.io/design-comuni-prototipi/"><?php esc_html_e( 'Prototipo per siti PA di AgID', 'italiawp2' ); ?></a>
						</li>
					</ul>
					<p class="small text-muted mb-0 mt-3">
						<?php esc_html_e( 'Il layout si ispira a Bootstrap Italia e al prototipo AgID; cio non implica approvazione o certificazione AgID del sito.', 'italiawp2' ); ?>
					</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal"><?php esc_html_e( 'Chiudi', 'italiawp2' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'italiawp3_render_footer_credits_modal', 5 );
