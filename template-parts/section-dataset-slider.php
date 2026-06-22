<?php
/*
 * ### SEZIONE DATASET IN EVIDENZA ###
 * Slider Swiper con card caricate via AJAX (skeleton durante il load).
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
?>
<section id="dataset-slider" class="section dataset-slider-section u-background-95 is-loading" data-loading="1">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="titolosezione">
					<h2 class="mb-0 text-white"><?php echo esc_html__( 'Dataset in evidenza', 'italiawp2' ); ?></h2>
				</div>
			</div>
		</div>
		<div class="row mt-3">
			<div class="col-12">
				<div class="swiper dataset-swiper" aria-busy="true">
					<div class="swiper-wrapper" id="dataset-swiper-wrapper">
						<?php echo italiawp2_dataset_slider_render_skeleton( 6 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="swiper-pagination"></div>
					<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Precedente', 'italiawp2' ); ?>"></div>
					<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Successivo', 'italiawp2' ); ?>"></div>
				</div>
			</div>
		</div>
	</div>
</section>
