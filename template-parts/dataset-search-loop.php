<?php
/*
 * Ricerca dataset nell'archivio /dataset/ — risultati in card.
 */
?>

<section id="sezione-notizie" class="pt64 pb32 bg-grigio opendata-dataset-search-section">
	<div class="container">

		<?php
		$paged        = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1;
		$search_value = get_search_query();
		$archive_link = class_exists( 'OpenData_Pa_Dataset' )
			? get_post_type_archive_link( OpenData_Pa_Dataset::POST_TYPE )
			: get_post_type_archive_link( 'opendata_dataset' );
		?>

		<div class="row">
			<div class="col-md-12">
				<div class="titolosezione">
					<h3>
						<?php
						if ( $search_value !== '' ) {
							printf(
								/* translators: %s: search query */
								esc_html__( 'Risultati per: %s', 'italiawp2' ),
								esc_html( $search_value )
							);
						} else {
							esc_html_e( 'Cerca dataset', 'italiawp2' );
						}
						?>
						<?php if ( $wp_query->max_num_pages !== 0 ) : ?>
							<br><small><?php echo esc_html__( 'Page', 'italiawp2' ); ?> <?php echo (int) $paged; ?> <?php echo esc_html__( 'of', 'italiawp2' ); ?> <?php echo (int) $wp_query->max_num_pages; ?></small>
						<?php endif; ?>
					</h3>
				</div>
			</div>
		</div>

		<?php if ( $archive_link ) : ?>
		<div class="row mb-3">
			<div class="col-md-12">
				<form role="search" method="get" class="opendata-archive-search-form" action="<?php echo esc_url( $archive_link ); ?>">
					<div class="input-group">
						<label class="screen-reader-text" for="opendata-archive-search"><?php esc_html_e( 'Cerca dataset', 'italiawp2' ); ?></label>
						<input type="search" id="opendata-archive-search" class="form-control" placeholder="<?php esc_attr_e( 'Cerca dataset...', 'italiawp2' ); ?>" value="<?php echo esc_attr( $search_value ); ?>" name="s">
						<button class="btn btn-primary" type="submit"><?php esc_html_e( 'Cerca', 'italiawp2' ); ?></button>
					</div>
					<input type="hidden" name="post_type" value="opendata_dataset">
				</form>
			</div>
		</div>
		<?php endif; ?>

		<p class="opendata-dataset-search-count mb-3">
			<?php
			printf(
				esc_html__( 'Dataset trovati: %d', 'italiawp2' ),
				(int) $wp_query->found_posts
			);
			?>
		</p>

		<div class="row row-eq-height opendata-dataset-search-grid">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<div class="col-md-6 col-lg-4 mb-4">
						<?php echo italiawp2_opendata_dataset_card_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endwhile; ?>
			<?php else : ?>
				<div class="col-12">
					<?php get_template_part( 'template-parts/error' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/pagination' ); ?>
