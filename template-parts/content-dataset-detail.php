<?php
/**
 * Contenuto pagina singola Dataset: layout a card (breadcrumb, identificazione, descrizione, contatto, distribuzioni, dati, metadati DCAT).
 * Richiede plugin Open Data PA. Allineato alle LG AgID su licenze, temi URI, metadati machine-readable.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
if ( ! class_exists( 'OpenData_Pa_Dataset' ) ) {
	return;
}

$post_id   = get_the_ID();
$wp_attachments = apply_filters( 'opendata_show_dataset_post_attachments', true )
	? (
		class_exists( 'OpenData_Pa_Dataset' )
			? OpenData_Pa_Dataset::get_dataset_allegati_attachments( $post_id )
			: get_attached_media( '', $post_id )
	)
	: array();
$url_allegati = class_exists( 'OpenData_Pa_Dataset' )
	? OpenData_Pa_Dataset::get_dataset_url_allegati_for_display( $post_id )
	: array();
$org_id    = OpenData_Pa_Dataset::get_organization_id( $post_id );
$org_name  = $org_id && class_exists( 'OpenData_Pa_Organization' ) ? OpenData_Pa_Organization::get_name( $org_id ) : '';
$org_code  = $org_id && class_exists( 'OpenData_Pa_Organization' ) ? OpenData_Pa_Organization::get_identifier( $org_id ) : '';
$identifier = OpenData_Pa_Dataset::get_identifier( $post_id );
$contact_fn = get_post_meta( $post_id, OpenData_Pa_Dataset::META_CONTACT_FN, true );
$contact_email = get_post_meta( $post_id, OpenData_Pa_Dataset::META_CONTACT_EMAIL, true );
$theme_uri = OpenData_Pa_Dataset::get_theme_uri( $post_id );
$accrual   = get_post_meta( $post_id, OpenData_Pa_Dataset::META_ACCRUAL_PERIODICITY, true );
$issued    = get_post_meta( $post_id, OpenData_Pa_Dataset::META_ISSUED, true );
$language_uri_display = trim( (string) get_post_meta( $post_id, OpenData_Pa_Dataset::META_LANGUAGE, true ) );
if ( $language_uri_display === '' ) {
	$language_uri_display = OpenData_Pa_Dataset::DEFAULT_LANGUAGE_URI;
}
$lang_label = OpenData_Pa_Dataset::get_language_label_for_display( $post_id );
$accrual_label = OpenData_Pa_Dataset::get_accrual_label_for_display( $post_id );
$keywords_list = OpenData_Pa_Dataset::get_keywords_list( $post_id );
$terms_list = class_exists( 'OpenData_Pa_Groups' ) && taxonomy_exists( OpenData_Pa_Groups::TAXONOMY ) ? get_the_terms( $post_id, OpenData_Pa_Groups::TAXONOMY ) : array();
if ( ! is_array( $terms_list ) ) {
	$terms_list = array();
}
$resources = class_exists( 'OpenData_Pa_Resource' ) ? OpenData_Pa_Resource::get_by_dataset( $post_id ) : array();
$api_endpoints = OpenData_Pa_Dataset::get_public_api_endpoints( $post_id );
$show_open_format_notice = class_exists( 'OpenData_Pa_Resource' ) && OpenData_Pa_Resource::dataset_lacks_structured_open_format( $post_id );

$archive_url = get_post_type_archive_link( OpenData_Pa_Dataset::POST_TYPE );
$archive_label = __( 'Area dataset', 'italiawp2' );
?>

<section id="dataset-detail" class="section dataset-detail-section">
	<div class="container-fluid dataset-detail-container">

		<!-- Card: Titolo e punteggio metadati -->
		<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card">
			<div class="card-body">
				<?php if ( apply_filters( 'opendata_show_metadata_score', false ) ) : ?>
					<div class="dataset-detail-meta-score small text-muted mb-2 p-2 bg-light rounded">
						<?php esc_html_e( 'Punteggio qualita metadati (nota europa.eu)', 'italiawp2' ); ?>: <?php esc_html_e( 'Non disponibile o identificazione non al livello', 'italiawp2' ); ?>
					</div>
				<?php endif; ?>
				<div class="row align-items-center g-3">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="col-12 col-sm-auto text-center text-sm-start">
							<?php
							echo get_the_post_thumbnail(
								$post_id,
								'medium',
								array(
									'class' => 'dataset-detail-cover-img img-fluid rounded border',
									'style' => 'max-width:min(100%, 280px); height:auto;',
									'alt'   => esc_attr( get_the_title() ),
								)
							);
							?>
						</div>
					<?php endif; ?>
					<div class="col">
						<h1 class="dataset-detail-title h2 mb-0"><?php the_title(); ?></h1>
						<div class="dataset-detail-content mt-3">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if ( $show_open_format_notice ) : ?>
			<div class="alert alert-warning" role="alert">
				<?php esc_html_e( 'Le distribuzioni non risultano in formati strutturati tipicamente machine-readable (es. CSV, JSON, XML, fogli di calcolo). Le linee guida AgID raccomandano di pubblicare almeno un formato aperto e riutilizzabile oltre a eventuali PDF.', 'italiawp2' ); ?>
			</div>
		<?php endif; ?>

		<!-- Card: Identificazione (Titolare, Editore, Codice IPA) -->
		<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card">
			<div class="card-body">
				<div class="row g-3 align-items-start dataset-detail-owner-row">
					<div class="col-12 col-md-6">
						<p class="mb-0"><strong><?php esc_html_e( 'Titolare - Nome', 'italiawp2' ); ?></strong><br><?php echo $org_name ? esc_html( $org_name ) : '&ndash;'; ?></p>
					</div>
					<div class="col-12 col-md-6">
						<p class="mb-0"><strong><?php esc_html_e( 'Editore', 'italiawp2' ); ?></strong><br><?php echo $org_name ? esc_html( $org_name ) : '&ndash;'; ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Card: URI, Temi, Parole chiave, Date (descrizione nel contenuto sopra) -->
		<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card">
			<div class="card-body">
				<div class="row g-3 dataset-detail-meta-grid">
					<div class="col-12">
						<p class="mb-0"><strong><?php esc_html_e( 'URI', 'italiawp2' ); ?></strong><br><code class="small"><?php echo esc_html( $identifier ); ?></code></p>
					</div>
					<?php if ( ! empty( $terms_list ) ) : ?>
						<div class="col-12<?php echo $theme_uri !== '' ? ' col-md-6' : ''; ?>">
							<p class="mb-0"><strong><?php esc_html_e( 'Temi', 'italiawp2' ); ?></strong><br><?php echo esc_html( implode( ', ', wp_list_pluck( $terms_list, 'name' ) ) ); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( $theme_uri !== '' ) : ?>
						<div class="col-12<?php echo ! empty( $terms_list ) ? ' col-md-6' : ''; ?>">
							<p class="mb-0"><strong><?php esc_html_e( 'Tema (URI data-theme EU)', 'italiawp2' ); ?></strong><br><a class="small" href="<?php echo esc_url( $theme_uri ); ?>" rel="noopener"><?php echo esc_html( $theme_uri ); ?></a></p>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $keywords_list ) ) : ?>
						<div class="col-12">
							<p class="mb-1"><strong><?php esc_html_e( 'Parole chiave', 'italiawp2' ); ?></strong></p>
							<div class="dataset-detail-keywords mb-0">
								<?php foreach ( $keywords_list as $kw ) : ?>
									<span class="badge rounded-pill bg-primary text-white me-2 mb-2"><?php echo esc_html( $kw ); ?></span>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="col-12 col-md-4">
						<p class="mb-0"><strong><?php esc_html_e( 'Data di rilascio', 'italiawp2' ); ?></strong><br><?php echo $issued ? esc_html( $issued ) : esc_html( get_the_date( 'Y-m-d' ) ); ?></p>
					</div>
					<div class="col-12 col-md-4">
						<p class="mb-0"><strong><?php esc_html_e( 'Frequenza di aggiornamento', 'italiawp2' ); ?></strong><br>
							<?php echo esc_html( $accrual_label ); ?>
						
						</p>
					</div>
					<div class="col-12 col-md-4">
						<p class="mb-0"><strong><?php esc_html_e( 'Data di ultima modifica', 'italiawp2' ); ?></strong><br><?php echo esc_html( get_the_modified_date( 'Y-m-d' ) ); ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Card: Punto di contatto -->
		<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card">
			<div class="card-body">
				<h3 class="h6 text-uppercase mb-3"><?php esc_html_e( 'Punto di contatto', 'italiawp2' ); ?></h3>
				<div class="row g-3 dataset-detail-contact-grid">
					<div class="col-12 col-md-6">
						<p class="mb-0"><strong><?php esc_html_e( 'Nome', 'italiawp2' ); ?></strong><br><?php echo $contact_fn ? esc_html( $contact_fn ) : ( $org_name ? esc_html( $org_name ) : '&ndash;' ); ?></p>
					</div>
					<div class="col-12 col-md-6">
						<p class="mb-0"><strong><?php esc_html_e( 'Email', 'italiawp2' ); ?></strong><br><?php echo $contact_email ? '<a href="mailto:' . esc_attr( $contact_email ) . '">' . esc_html( $contact_email ) . '</a>' : '&ndash;'; ?></p>
					</div>
				</div>
			</div>
		</div>

		<!-- Card: Lingua, accesso, metadati DCAT / API -->
		<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card">
			<div class="card-body">
				<h3 class="h6 text-uppercase mb-3"><?php esc_html_e( 'Lingua e accesso', 'italiawp2' ); ?></h3>
				<div class="row g-3 dataset-detail-lang-grid">
					<div class="col-12 col-md-6">
						<p class="mb-1"><strong><?php esc_html_e( 'Lingua del dataset', 'italiawp2' ); ?></strong><br><?php echo esc_html( $lang_label ); ?></p>
					</div>
					<div class="col-12 col-md-6">
						<p class="mb-0"><strong><?php esc_html_e( 'Pagina di accesso', 'italiawp2' ); ?></strong><br><a href="<?php the_permalink(); ?>"><?php the_permalink(); ?></a></p>
					</div>
				</div>
				<?php if ( ! empty( $api_endpoints ) ) : ?>
					<hr class="my-3">
					<h3 class="h6 text-uppercase mb-3"><?php esc_html_e( 'Metadati machine-readable (DCAT / API)', 'italiawp2' ); ?></h3>
					<div class="row g-2 g-md-3 small dataset-detail-api-grid">
						<div class="col-12 col-sm-6 col-lg-3">
							<a href="<?php echo esc_url( $api_endpoints['dataset_jsonld'] ); ?>" class="d-inline-block text-break"><?php esc_html_e( 'Dataset in JSON-LD (DCAT)', 'italiawp2' ); ?></a>
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<a href="<?php echo esc_url( $api_endpoints['catalog_jsonld'] ); ?>" class="d-inline-block text-break"><?php esc_html_e( 'Catalogo in JSON-LD', 'italiawp2' ); ?></a>
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<a href="<?php echo esc_url( $api_endpoints['package_show'] ); ?>" class="d-inline-block text-break"><?php esc_html_e( 'Dataset (API JSON)', 'italiawp2' ); ?></a>
						</div>
						<div class="col-12 col-sm-6 col-lg-3">
							<a href="<?php echo esc_url( $api_endpoints['openapi'] ); ?>" class="d-inline-block text-break"><?php esc_html_e( 'Documentazione OpenAPI', 'italiawp2' ); ?></a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Card: Distribuzioni -->
		<?php if ( ! empty( $resources ) ) : ?>
			<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card dataset-detail-card--distributions">
				<div class="card-body">
					<section class="dataset-detail-distributions" aria-labelledby="dataset-detail-distributions-heading">
						<div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-2 mb-3">
							<div>
								<h3 id="dataset-detail-distributions-heading" class="h6 text-uppercase mb-1"><?php esc_html_e( 'Distribuzioni', 'italiawp2' ); ?></h3>
								<p class="small text-muted mb-0"><?php esc_html_e( 'Distribuzioni DCAT', 'italiawp2' ); ?></p>
							</div>
						</div>
						<ul class="list-unstyled mb-0 dataset-detail-distributions-list" role="list">
							<?php
							$res_count = count( $resources );
							$idx       = 0;
							$table_limit = (int) apply_filters( 'opendata_pa_dataset_table_limit', 500 );
							foreach ( $resources as $res ) :
								++$idx;
								$dist_src_label = OpenData_Pa_Resource::get_distribution_source_label( $res->ID );
								$res_url = OpenData_Pa_Resource::get_access_url( $res->ID );
								$table_preview_url = '';
								if ( $res_url && class_exists( 'OpenData_Pa_CSV' ) ) {
									$parsed_rows = OpenData_Pa_CSV::parse( $res_url, $table_limit );
									$looks_like_table = ! empty( $parsed_rows ) && is_array( $parsed_rows[0] ) && strpos( implode( ' ', $parsed_rows[0] ), '<' ) === false;
									if ( $looks_like_table ) {
										$table_preview_url = OpenData_Pa_Resource::get_table_preview_url( $post_id, $res->ID );
									}
								}
								$format_uri = OpenData_Pa_Resource::get_format( $res->ID );
								$format_label = $format_uri ? strtoupper( preg_replace( '#^.*/#', '', rtrim( $format_uri, '/' ) ) ) : '';
								if ( $format_label === '' ) {
									$format_label = __( 'Dati', 'italiawp2' );
								}
								$res_title = $res->post_title ? $res->post_title : __( 'Distribuzione', 'italiawp2' );
								$lic_uri = OpenData_Pa_Resource::get_license_uri_effective( $res->ID );
								$lic_label = OpenData_Pa_Resource::get_license_label_for_display( $res->ID );
								$is_last = ( $idx === $res_count );
								$download_aria = sprintf(
									/* translators: %s: distribution title */
									__( 'Scarica la risorsa: %s', 'italiawp2' ),
									$res_title
								);
								$view_aria = sprintf(
									/* translators: %s: distribution title */
									__( 'Visualizza la tabella dati: %s', 'italiawp2' ),
									$res_title
								);
								?>
								<li
									class="dataset-detail-distribution rounded border p-3 p-md-4 mb-3<?php echo $is_last ? ' mb-0' : ''; ?> bg-light"
									role="listitem"
									id="<?php echo esc_attr( 'dataset-distribution-' . $res->ID ); ?>"
								>
									<div class="row g-3 align-items-start">
										<div class="col-12 col-lg">
											<div class="d-flex flex-wrap align-items-center gap-3 mb-2">
												<span class="badge rounded-pill bg-success text-white me-2"><?php echo esc_html( $format_label ); ?></span>
												<?php if ( $dist_src_label !== '' ) : ?>
													<span class="badge rounded-pill bg-info text-white" title="<?php echo esc_attr__( 'Distribuzione da URL esterno', 'italiawp2' ); ?>"><?php echo esc_html( $dist_src_label ); ?></span>&nbsp;
												<?php endif; ?>&nbsp;
												<h4 class="h6 mb-0 text-break"><?php echo esc_html( $res_title ); ?></h4>
											</div>
											<dl class="row mb-0 small dataset-detail-distribution-license">
												<dt class="col-sm-4 col-md-3 text-muted fw-normal mb-1 mb-sm-0"><?php esc_html_e( 'Licenza di riutilizzo', 'italiawp2' ); ?></dt>
												<dd class="col-sm-8 col-md-9 mb-0">&nbsp;
													<a href="<?php echo esc_url( $lic_uri ); ?>" class="text-break" rel="license noopener noreferrer"><?php echo esc_html( $lic_label ); ?>&nbsp;</a>
												</dd>
											</dl>
										</div>
										<?php if ( $res_url ) : ?>
											<div class="col-12 col-lg-auto d-flex flex-wrap gap-3 align-items-center justify-content-lg-end">
												<?php if ( $table_preview_url !== '' ) : ?>
													<a
														href="<?php echo esc_url( $table_preview_url ); ?>"
														class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2 me-2"
														target="_blank"
														rel="noopener noreferrer"
														aria-label="<?php echo esc_attr( $view_aria ); ?>"
													>
														<svg class="icon icon-sm" aria-hidden="true" focusable="false"><use href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/svg/sprite.svg#it-fullscreen"></use></svg>
														<span><?php esc_html_e( 'Visualizza', 'italiawp2' ); ?></span>
													</a>&nbsp;
												<?php endif; ?>
												<a
													href="<?php echo esc_url( $res_url ); ?>"
													class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2"
													target="_blank"
													rel="noopener noreferrer"
													aria-label="<?php echo esc_attr( $download_aria ); ?>"
												>
													<svg class="icon icon-sm" aria-hidden="true" focusable="false"><use href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/svg/sprite.svg#it-download"></use></svg>
													<span><?php esc_html_e( 'Scarica', 'italiawp2' ); ?></span>
												</a>
											</div>
										<?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $wp_attachments ) || ! empty( $url_allegati ) ) : ?>
			<div class="card card-wrapper border shadow-sm mb-4 dataset-detail-card dataset-detail-card--attachments">
				<div class="card-body">
					<section class="dataset-detail-attachments" aria-labelledby="dataset-detail-attachments-heading">
						<h3 id="dataset-detail-attachments-heading" class="h6 text-uppercase mb-2"><?php esc_html_e( 'Allegati', 'italiawp2' ); ?></h3>
						<p class="small text-muted mb-3"><?php esc_html_e( 'Documenti e collegamenti a risorse esterne.', 'italiawp2' ); ?></p>
						<ul class="list-unstyled mb-0" role="list">
							<?php
							$att_idx = 0;
							$att_total = count( $wp_attachments ) + count( $url_allegati );
							foreach ( $wp_attachments as $watt ) :
								++$att_idx;
								$att_url = wp_get_attachment_url( $watt->ID );
								$att_path = get_attached_file( $watt->ID );
								$att_name = $watt->post_title ? $watt->post_title : ( $att_path ? basename( $att_path ) : __( 'File', 'italiawp2' ) );
								$is_att_last = ( $att_idx === $att_total );
								?>
								<li class="d-flex flex-wrap align-items-center gap-3 mb-3<?php echo $is_att_last ? ' mb-0' : ''; ?>" role="listitem">
									<span class="badge rounded-pill bg-secondary text-white flex-shrink-0 me-2"><?php esc_html_e( 'Allegato', 'italiawp2' ); ?></span>&nbsp;
									<?php if ( $att_url ) : ?>
										<a href="<?php echo esc_url( $att_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-break"><?php echo esc_html( $att_name ); ?></a>
									<?php else : ?>
										<span class="text-break"><?php echo esc_html( $att_name ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
							<?php foreach ( $url_allegati as $ua ) : ?>
								<?php
								++$att_idx;
								$ua_url = isset( $ua['url'] ) ? $ua['url'] : '';
								$ua_title = isset( $ua['title'] ) ? trim( (string) $ua['title'] ) : '';
								$label = $ua_title !== '' ? $ua_title : $ua_url;
								$is_att_last = ( $att_idx === $att_total );
								?>
								<li class="d-flex flex-wrap align-items-center gap-3 mb-3<?php echo $is_att_last ? ' mb-0' : ''; ?>" role="listitem">
									<span class="badge rounded-pill bg-info text-white flex-shrink-0 me-2"><?php esc_html_e( 'Link', 'italiawp2' ); ?></span>&nbsp;
									<?php if ( $ua_url !== '' ) : ?>
										<a href="<?php echo esc_url( $ua_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-break"><?php echo esc_html( $label ); ?></a>
									<?php else : ?>
										<span class="text-break"><?php echo esc_html( $label ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				</div>
			</div>
		<?php endif; ?>

		<?php
		if ( class_exists( 'OpenData_Pa_Dataset' ) ) {
			OpenData_Pa_Dataset::render_embed_block( $post_id );
		}
		?>

		<?php
		if ( class_exists( 'OpenData_Pa_Chart_Map' ) ) :
			$has_chart = OpenData_Pa_Chart_Map::get_chart_config( $post_id );
			$has_map   = OpenData_Pa_Chart_Map::get_map_config( $post_id );
			if ( $has_chart ) :
				OpenData_Pa_Chart_Map::render_chart( $post_id );
			endif;
			if ( $has_map ) :
				OpenData_Pa_Chart_Map::render_map( $post_id );
			endif;
		endif;
		?>

		<?php italiawp3_render_dataset_share_block( $post_id ); ?>
	</div>
</section>
