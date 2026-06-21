<?php
/*
 * Sezione catalogo dataset (home): ricerca, filtri sidebar, elenco card e paginazione.
 * Richiede plugin Open Data PA.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
if ( ! get_theme_mod( 'active_section_dataset_catalog', true ) ) {
	return;
}
if ( ! class_exists( 'OpenData_Pa_Dataset' ) || ! class_exists( 'OpenData_Pa_Resource' ) || ! class_exists( 'OpenData_Pa_Organization' ) ) {
	return;
}

$p = italiawp2_opendata_catalog_parse_params( $_GET );
$args = italiawp2_opendata_catalog_query_args( $p );
$query = new WP_Query( $args );

$orgs = get_posts( array( 'post_type' => OpenData_Pa_Organization::POST_TYPE, 'posts_per_page' => -1, 'orderby' => 'title', 'post_status' => 'publish' ) );
$groups = array();
if ( class_exists( 'OpenData_Pa_Groups' ) && taxonomy_exists( OpenData_Pa_Groups::TAXONOMY ) ) {
	$groups = get_terms( array( 'taxonomy' => OpenData_Pa_Groups::TAXONOMY, 'hide_empty' => true ) );
	if ( is_wp_error( $groups ) ) {
		$groups = array();
	}
}
$format_counts = function_exists( 'italiawp2_opendata_catalog_format_counts' ) ? italiawp2_opendata_catalog_format_counts() : array();
if ( ! empty( $format_counts ) ) {
	ksort( $format_counts, SORT_STRING );
}
$tag_counts = function_exists( 'italiawp2_opendata_catalog_dataset_tag_counts' ) ? italiawp2_opendata_catalog_dataset_tag_counts() : array();
?>

<section id="dataset-catalog" class="section dataset-catalog-section u-background-95">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<div class="titolosezione">
					<h2 class="mb-0 text-white"><?php esc_html_e( 'Catalogo dataset', 'italiawp2' ); ?></h2>
				</div>
			</div>
		</div>

		<form method="get" id="opendata-catalog-form" class="row mt-3">
			<?php if ( ! get_option( 'permalink_structure' ) ) : ?>
				<input type="hidden" name="page_id" value="<?php echo (int) get_queried_object_id(); ?>">
			<?php endif; ?>

			<aside class="col-lg-4 col-md-12 mb-4 mb-lg-0">
				<div class="opendata-catalog-filters card card-wrapper border-0 shadow-sm">
					<div class="card-body">
						<h3 class="h6 mb-3"><?php esc_html_e( 'Filtra e ordina', 'italiawp2' ); ?></h3>

						<div class="mb-3">
							<label for="opendata_s" class="form-label"><?php esc_html_e( 'Per titolo e descrizione', 'italiawp2' ); ?></label>
							<div class="input-group">
								<span class="input-group-text" aria-hidden="true"><svg class="icon icon-sm" aria-hidden="true"><use href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/svg/sprite.svg#it-search"></use></svg></span>
								<input type="search" id="opendata_s" name="opendata_s" class="form-control" value="<?php echo esc_attr( $p['search'] ); ?>" placeholder="<?php esc_attr_e( 'Cerca...', 'italiawp2' ); ?>" autocomplete="off">
							</div>
						</div>

						<div class="mb-3">
							<label for="opendata_order" class="form-label"><?php esc_html_e( 'Ordina per', 'italiawp2' ); ?></label>
							<select id="opendata_order" name="opendata_order" class="form-select">
								<option value=""><?php esc_html_e( 'Scegli una opzione', 'italiawp2' ); ?></option>
								<option value="modified" <?php selected( $p['order_key'], 'modified' ); ?>><?php esc_html_e( 'Data ultima modifica', 'italiawp2' ); ?></option>
								<option value="date" <?php selected( $p['order_key'], 'date' ); ?>><?php esc_html_e( 'Data pubblicazione', 'italiawp2' ); ?></option>
								<option value="title" <?php selected( $p['order_key'], 'title' ); ?>><?php esc_html_e( 'Titolo', 'italiawp2' ); ?></option>
							</select>
						</div>

						<?php if ( ! empty( $orgs ) ) : ?>
						<div class="mb-3">
							<label for="opendata_org" class="form-label"><?php esc_html_e( 'Organizzazione', 'italiawp2' ); ?></label>
							<select id="opendata_org" name="opendata_org" class="form-select">
								<option value=""><?php esc_html_e( 'Tutte', 'italiawp2' ); ?></option>
								<?php foreach ( $orgs as $o ) : ?>
									<option value="<?php echo (int) $o->ID; ?>" <?php selected( $p['org_id'], (int) $o->ID ); ?>><?php echo esc_html( $o->post_title ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $groups ) ) : ?>
						<div class="mb-3">
							<label for="opendata_group" class="form-label"><?php esc_html_e( 'Temi / Gruppi', 'italiawp2' ); ?></label>
							<select id="opendata_group" name="opendata_group" class="form-select">
								<option value=""><?php esc_html_e( 'Tutti', 'italiawp2' ); ?></option>
								<?php foreach ( $groups as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $p['group_slug'], $term->slug ); ?>>
										<?php echo esc_html( $term->name ); ?> (<?php echo (int) $term->count; ?>)
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $format_counts ) ) : ?>
						<div class="mb-3">
							<p class="form-label mb-2"><?php esc_html_e( 'Formati', 'italiawp2' ); ?></p>
							<div class="opendata-catalog-format-checks">
								<?php
								$sel_formats = isset( $p['formats'] ) ? $p['formats'] : array();
								foreach ( $format_counts as $fmt_slug => $fmt_count ) :
									$fid = 'opendata_fmt_' . preg_replace( '/[^a-z0-9_-]/i', '', $fmt_slug );
									?>
									<div class="form-check">
										<input
											type="checkbox"
											class="form-check-input opendata-catalog-format-filter"
											name="opendata_formats[]"
											value="<?php echo esc_attr( $fmt_slug ); ?>"
											id="<?php echo esc_attr( $fid ); ?>"
											<?php checked( in_array( $fmt_slug, $sel_formats, true ) ); ?>
										>
										<label class="form-check-label" for="<?php echo esc_attr( $fid ); ?>">
											<?php echo esc_html( strtoupper( $fmt_slug ) ); ?> (<?php echo (int) $fmt_count; ?>)
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $tag_counts ) ) : ?>
						<div class="mb-0">
							<p class="form-label mb-2"><?php esc_html_e( 'Tag', 'italiawp2' ); ?></p>
							<div class="opendata-catalog-tag-checks">
								<?php
								$sel_tags = isset( $p['tags'] ) ? $p['tags'] : array();
								foreach ( $tag_counts as $tag_slug => $tag_info ) :
									$name  = isset( $tag_info['name'] ) ? $tag_info['name'] : $tag_slug;
									$tc    = isset( $tag_info['count'] ) ? (int) $tag_info['count'] : 0;
									$tid   = 'opendata_tag_' . preg_replace( '/[^a-z0-9_-]/i', '', $tag_slug );
									?>
									<div class="form-check">
										<input
											type="checkbox"
											class="form-check-input opendata-catalog-tag-filter"
											name="opendata_tags[]"
											value="<?php echo esc_attr( $tag_slug ); ?>"
											id="<?php echo esc_attr( $tid ); ?>"
											<?php checked( in_array( $tag_slug, $sel_tags, true ) ); ?>
										>
										<label class="form-check-label" for="<?php echo esc_attr( $tid ); ?>">
											<?php echo esc_html( $name ); ?> (<?php echo $tc; ?>)
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</aside>

			<div class="col-lg-8 col-md-12">
				<div id="opendata-catalog-results" class="opendata-catalog-results" aria-live="polite" aria-busy="false">
					<div class="opendata-catalog-results-inner">
						<?php echo italiawp2_opendata_catalog_results_html( $query, $p ); ?>
					</div>
					<div class="opendata-catalog-loader-overlay" aria-hidden="true">
						<div class="spinner-border text-white" style="width:3rem;height:3rem;" role="status">
 						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</section>
