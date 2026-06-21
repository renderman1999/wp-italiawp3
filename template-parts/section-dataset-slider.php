<?php
/*
 * ### SEZIONE DATASET IN EVIDENZA ###
 * Slider Swiper con card dei dataset in evidenza (plugin Open Data PA).
 * Immagine di copertina = immagine in evidenza del post (thumbnail).
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
if ( ! get_theme_mod( 'active_section_dataset_slider', true ) ) {
	return;
}
if ( ! class_exists( 'OpenData_Pa_Dataset' ) || ! method_exists( 'OpenData_Pa_Dataset', 'get_featured_for_slider' ) ) {
	return;
}
$datasets = OpenData_Pa_Dataset::get_featured_for_slider( 12 );
if ( empty( $datasets ) ) {
	return;
}
$placeholder_img = get_theme_mod( 'immagine_evidenza_default', get_template_directory_uri() . '/images/400x220.png' );
if ( ! $placeholder_img ) {
	$placeholder_img = get_template_directory_uri() . '/images/400x220.png';
}
?>
<section id="dataset-slider" class="section dataset-slider-section u-background-95">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<div class="titolosezione">
					<h2 class="mb-0 text-white"><?php echo esc_html__( 'Dataset in evidenza', 'italiawp2' ); ?></h2>
				</div>
			</div>
		</div>
		<div class="row mt-3">
			<div class="col-12">
				<div class="swiper dataset-swiper">
					<div class="swiper-wrapper">
						<?php foreach ( $datasets as $dataset_post ) : ?>
							<?php
							$post = $dataset_post;
							setup_postdata( $post );
							$img_url = '';
							$thumb_id = get_post_thumbnail_id( $post->ID );
							if ( $thumb_id ) {
								$img_arr = wp_get_attachment_image_src( $thumb_id, 'medium_large' );
								if ( ! empty( $img_arr[0] ) ) {
									$img_url = $img_arr[0];
								}
							}
							if ( ! $img_url ) {
								$img_url = esc_url( $placeholder_img );
							}
							$permalink = get_permalink( $post->ID );
							$title    = get_the_title( $post->ID );
							$excerpt  = has_excerpt( $post->ID ) ? get_the_excerpt( $post->ID ) : wp_trim_words( get_the_content( null, false, $post->ID ), 20 );
							?>
							<div class="swiper-slide">
								<article class="card card-bg card-big border-0 shadow-sm h-100">
									<div class="card-img-top-wrapper">
										<a href="<?php echo esc_url( $permalink ); ?>" class="d-block">
											<img src="<?php echo esc_url( $img_url ); ?>" class="card-img-top" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
										</a>
									</div>
									<div class="card-body">
										<h3 class="card-title h5">
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
						<?php endforeach; ?>
						<?php wp_reset_postdata(); ?>
					</div>
					<div class="swiper-pagination"></div>
					<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Precedente', 'italiawp2' ); ?>"></div>
					<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Successivo', 'italiawp2' ); ?>"></div>
				</div>
			</div>
		</div>
	</div>
</section>
