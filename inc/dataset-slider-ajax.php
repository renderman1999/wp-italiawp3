<?php
/**
 * Slider Dataset in evidenza (home): AJAX + cache HTML slide.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 *
 * @package ItaliaWP2
 */

defined( 'ABSPATH' ) || exit;

const ITALIAWP2_DATASET_SLIDER_TRANSIENT = 'italiawp2_featured_slider_html_v3';

/**
 * @return string
 */
function italiawp2_dataset_slider_placeholder_img() {
	$placeholder = get_theme_mod( 'immagine_evidenza_default', get_template_directory_uri() . '/images/400x220.png' );
	if ( ! $placeholder ) {
		$placeholder = get_template_directory_uri() . '/images/400x220.png';
	}
	return esc_url( $placeholder );
}

/**
 * HTML skeleton slide per caricamento AJAX.
 *
 * @param int $count Numero slide placeholder.
 * @return string
 */
function italiawp2_dataset_slider_render_skeleton( $count = 6 ) {
	$count = max( 1, min( 6, (int) $count ) );
	ob_start();
	for ( $i = 0; $i < $count; $i++ ) :
		?>
		<div class="swiper-slide dataset-slider-skeleton-slide" aria-hidden="true">
			<article class="card card-bg card-big border-0 shadow-sm h-100 dataset-slider-card dataset-slider-skeleton">
				<div class="dataset-slider-skeleton__media"></div>
				<div class="card-body">
					<div class="dataset-slider-skeleton__title"></div>
					<div class="dataset-slider-skeleton__text"></div>
					<div class="dataset-slider-skeleton__text dataset-slider-skeleton__text--short"></div>
					<div class="dataset-slider-skeleton__btn"></div>
				</div>
			</article>
		</div>
		<?php
	endfor;
	return ob_get_clean();
}

/**
 * HTML slide dataset per Swiper.
 *
 * @param WP_Post[] $datasets Post dataset.
 * @return string
 */
function italiawp2_dataset_slider_render_slides( $datasets ) {
	if ( empty( $datasets ) ) {
		return '';
	}

	$placeholder_img = italiawp2_dataset_slider_placeholder_img();
	ob_start();

	foreach ( $datasets as $dataset_post ) {
		$post = $dataset_post;
		$img_url = '';
		$thumb_id = get_post_thumbnail_id( $post->ID );
		if ( $thumb_id ) {
			$img_arr = wp_get_attachment_image_src( $thumb_id, 'medium_large' );
			if ( ! empty( $img_arr[0] ) ) {
				$img_url = $img_arr[0];
			}
		}
		if ( ! $img_url ) {
			$img_url = $placeholder_img;
		}

		$permalink = get_permalink( $post->ID );
		$title     = get_the_title( $post->ID );
		$excerpt   = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : wp_trim_words( get_the_content( null, false, $post->ID ), 12 );
		?>
		<div class="swiper-slide">
			<article class="card card-bg card-big border-0 shadow-sm h-100 dataset-slider-card">
				<div class="card-img-top-wrapper">
					<a href="<?php echo esc_url( $permalink ); ?>" class="d-block">
						<img src="<?php echo esc_url( $img_url ); ?>" class="dataset-slider-card__img" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
					</a>
				</div>
				<div class="card-body">
					<h3 class="card-title h6">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					</h3>
					<?php if ( $excerpt ) : ?>
						<p class="card-text small text-muted"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="btn btn-outline-primary btn-sm">
						<?php echo esc_html__( 'Apri dataset', 'italiawp2' ); ?>
					</a>
				</div>
			</article>
		</div>
		<?php
	}

	return ob_get_clean();
}

/**
 * Invalida cache slider quando cambia un dataset.
 *
 * @param int $post_id ID post.
 */
function italiawp2_dataset_slider_flush_cache( $post_id ) {
	if ( ! class_exists( 'OpenData_Pa_Dataset' ) ) {
		return;
	}
	if ( get_post_type( $post_id ) === OpenData_Pa_Dataset::POST_TYPE ) {
		delete_transient( ITALIAWP2_DATASET_SLIDER_TRANSIENT );
	}
}
add_action( 'save_post', 'italiawp2_dataset_slider_flush_cache' );
add_action( 'deleted_post', 'italiawp2_dataset_slider_flush_cache' );

/**
 * Invalida cache quando cambia il flag "in evidenza".
 *
 * @param int    $meta_id    ID meta.
 * @param int    $post_id    ID post.
 * @param string $meta_key   Chiave meta.
 * @param mixed  $meta_value Valore meta.
 */
function italiawp2_dataset_slider_flush_cache_on_meta( $meta_id, $post_id, $meta_key, $meta_value ) {
	unset( $meta_id, $meta_value );
	if ( ! class_exists( 'OpenData_Pa_Dataset' ) ) {
		return;
	}
	if ( get_post_type( $post_id ) === OpenData_Pa_Dataset::POST_TYPE && $meta_key === OpenData_Pa_Dataset::META_IN_EVIDENZA ) {
		delete_transient( ITALIAWP2_DATASET_SLIDER_TRANSIENT );
	}
}
add_action( 'updated_post_meta', 'italiawp2_dataset_slider_flush_cache_on_meta', 10, 4 );
add_action( 'added_post_meta', 'italiawp2_dataset_slider_flush_cache_on_meta', 10, 4 );
add_action( 'deleted_post_meta', 'italiawp2_dataset_slider_flush_cache_on_meta', 10, 4 );

/**
 * AJAX: HTML slide dataset in evidenza.
 */
function italiawp2_ajax_featured_datasets() {
	check_ajax_referer( 'italiawp2_dataset_slider', 'nonce' );

	if ( ! class_exists( 'OpenData_Pa_Dataset' ) || ! method_exists( 'OpenData_Pa_Dataset', 'get_featured_for_slider' ) ) {
		wp_send_json_error( array( 'message' => __( 'Open Data PA non attivo.', 'italiawp2' ) ), 400 );
	}

	$cached = get_transient( ITALIAWP2_DATASET_SLIDER_TRANSIENT );
	if ( is_array( $cached ) && isset( $cached['html'], $cached['count'] ) ) {
		wp_send_json_success( $cached );
	}

	$datasets = OpenData_Pa_Dataset::get_featured_for_slider( 12 );
	$count    = count( $datasets );
	$html     = $count > 0 ? italiawp2_dataset_slider_render_slides( $datasets ) : '';
	$payload  = array(
		'html'  => $html,
		'count' => $count,
	);

	set_transient( ITALIAWP2_DATASET_SLIDER_TRANSIENT, $payload, 5 * MINUTE_IN_SECONDS );

	wp_send_json_success( $payload );
}
add_action( 'wp_ajax_italiawp2_featured_datasets', 'italiawp2_ajax_featured_datasets' );
add_action( 'wp_ajax_nopriv_italiawp2_featured_datasets', 'italiawp2_ajax_featured_datasets' );
