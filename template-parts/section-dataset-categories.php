<?php
/*
 * Sezione "Naviga i dati per categoria tematica" (home): griglia di card per ogni gruppo/tema (tassonomia opendata_group).
 * Ogni card usa l'immagine di copertina del gruppo e linka all'archivio del gruppo.
 * Richiede plugin Open Data PA con supporto thumbnail sui gruppi.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
if ( ! get_theme_mod( 'active_section_dataset_categories', true ) ) {
	return;
}
if ( ! class_exists( 'OpenData_Pa_Groups' ) || ! taxonomy_exists( OpenData_Pa_Groups::TAXONOMY ) ) {
	return;
}

$groups = get_terms( array(
	'taxonomy'   => OpenData_Pa_Groups::TAXONOMY,
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );
if ( is_wp_error( $groups ) || empty( $groups ) ) {
	return;
}
?>

<section id="dataset-categories" class="section dataset-categories-section u-background-95">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<div class="titolosezione text-center">
					<h2 class="mb-0 text-white"><?php esc_html_e( 'Naviga i dati per categoria tematica', 'italiawp2' ); ?></h2>
				</div>
			</div>
		</div>
		<div class="row mt-3 g-3 g-md-4">
			<?php foreach ( $groups as $term ) :
				$term_link = get_term_link( $term );
				if ( is_wp_error( $term_link ) ) {
					continue;
				}
				$thumb_url = OpenData_Pa_Groups::get_thumbnail_url( $term->term_id, 'thumbnail' );
				?>
				<div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-4">
					<a href="<?php echo esc_url( $term_link ); ?>" class="dataset-category-card card border h-100 text-decoration-none">
						<div class="dataset-category-card-inner d-flex align-items-center">
							<div class="dataset-category-card-icon flex-shrink-0">
								<?php if ( $thumb_url ) : ?>
									<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" class="dataset-category-card-img" loading="lazy">
								<?php else : ?>
									<div class="dataset-category-card-placeholder" aria-hidden="true"></div>
								<?php endif; ?>
							</div>
							<div class="dataset-category-card-body flex-grow-1 min-w-0">
								<h3 class="dataset-category-card-title mb-0"><?php echo esc_html( $term->name ); ?>&nbsp;<span class="dataset-category-card-count badge rounded-pill text-bg-success"><?php if ( (int) $term->count > 0 ) { echo (int) $term->count; } ?></span>
								</h3>
							</div>
						</div>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
