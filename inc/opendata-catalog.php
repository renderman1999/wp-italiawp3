<?php
/**
 * Catalogo dataset (home): query condivisa, rendering risultati, AJAX filtri.
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 *
 * @package ItaliaWP2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Converte URI formato EU / MIME in etichetta badge corta (es. CSV, XLSX).
 *
 * @param string $uri URI file-type EU, MIME o stringa vuota.
 * @return string Es. CSV o stringa vuota se non determinabile.
 */
function italiawp2_opendata_format_uri_to_badge_label( $uri ) {
	$uri = rtrim( trim( (string) $uri ), '/' );
	if ( $uri === '' || $uri === 'application/octet-stream' ) {
		return '';
	}
	if ( preg_match( '~file-type/([^/?#]+)$~i', $uri, $m ) ) {
		return strtoupper( $m[1] );
	}
	if ( preg_match( '~^https?://~i', $uri ) ) {
		$last = preg_replace( '~^.*/~', '', rtrim( $uri, '/' ) );
		return $last !== '' ? strtoupper( $last ) : '';
	}
	$mime_map = array(
		'text/csv'     => 'CSV',
		'text/plain'   => 'TXT',
		'application/json' => 'JSON',
		'application/pdf'  => 'PDF',
		'application/xml'  => 'XML',
		'text/xml'         => 'XML',
		'application/vnd.ms-excel' => 'XLS',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
	);
	$key = strtolower( $uri );
	return isset( $mime_map[ $key ] ) ? $mime_map[ $key ] : '';
}

/**
 * Elenco etichette formato uniche per tutte le distribuzioni del dataset (ordine alfabetico).
 *
 * @param int $dataset_id ID post dataset.
 * @return string[] Es. array( 'CSV', 'JSON' ).
 */
function italiawp2_opendata_catalog_dataset_format_badges( $dataset_id ) {
	if ( ! class_exists( 'OpenData_Pa_Resource' ) ) {
		return array();
	}
	$resources = OpenData_Pa_Resource::get_by_dataset( $dataset_id );
	if ( empty( $resources ) ) {
		return array();
	}
	$seen = array();
	foreach ( $resources as $res ) {
		$uri   = OpenData_Pa_Resource::get_effective_format_uri( $res->ID );
		$label = italiawp2_opendata_format_uri_to_badge_label( $uri );
		if ( $label === '' ) {
			continue;
		}
		$seen[ strtolower( $label ) ] = $label;
	}
	$labels = array_values( $seen );
	sort( $labels, SORT_STRING | SORT_FLAG_CASE );
	return $labels;
}

/**
 * Slug formato (minuscolo) per ogni distribuzione del dataset.
 *
 * @param int $dataset_id ID post dataset.
 * @return string[] Es. array( 'csv', 'json' ).
 */
function italiawp2_opendata_catalog_dataset_format_slugs( $dataset_id ) {
	$badges = italiawp2_opendata_catalog_dataset_format_badges( $dataset_id );
	return array_map( 'strtolower', $badges );
}

/**
 * Conteggio dataset pubblicati per slug formato (almeno una distribuzione con quel formato).
 *
 * @return array<string, int> Es. array( 'csv' => 2, 'json' => 1 ).
 */
function italiawp2_opendata_catalog_format_counts() {
	$counts = array();
	if ( ! class_exists( 'OpenData_Pa_Dataset' ) ) {
		return $counts;
	}
	$dataset_ids = get_posts(
		array(
			'post_type'      => OpenData_Pa_Dataset::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	foreach ( $dataset_ids as $did ) {
		$slugs = italiawp2_opendata_catalog_dataset_format_slugs( (int) $did );
		foreach ( $slugs as $slug ) {
			if ( ! isset( $counts[ $slug ] ) ) {
				$counts[ $slug ] = 0;
			}
			$counts[ $slug ]++;
		}
	}
	return $counts;
}

/**
 * ID dataset che hanno almeno una distribuzione in uno degli slug formato richiesti (OR).
 *
 * @param string[] $format_slugs Slug minuscoli (es. csv, xlsx).
 * @return int[]
 */
function italiawp2_opendata_catalog_dataset_ids_for_formats( $format_slugs ) {
	$format_slugs = array_unique(
		array_filter(
			array_map(
				function ( $s ) {
					return strtolower( sanitize_key( (string) $s ) );
				},
				(array) $format_slugs
			)
		)
	);
	if ( empty( $format_slugs ) || ! class_exists( 'OpenData_Pa_Dataset' ) ) {
		return array();
	}
	$dataset_ids = get_posts(
		array(
			'post_type'      => OpenData_Pa_Dataset::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$out = array();
	foreach ( $dataset_ids as $did ) {
		$did   = (int) $did;
		$slugs = italiawp2_opendata_catalog_dataset_format_slugs( $did );
		foreach ( $format_slugs as $wanted ) {
			if ( in_array( $wanted, $slugs, true ) ) {
				$out[] = $did;
				break;
			}
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Tag (post_tag) con conteggio dataset pubblicati per ciascuno.
 *
 * @return array<string, array{name:string,count:int}> Chiave slug, valore nome e conteggio.
 */
function italiawp2_opendata_catalog_dataset_tag_counts() {
	global $wpdb;
	if ( ! class_exists( 'OpenData_Pa_Dataset' ) || ! taxonomy_exists( 'post_tag' ) ) {
		return array();
	}
	$pt = OpenData_Pa_Dataset::POST_TYPE;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT t.term_id, t.slug, t.name, COUNT(DISTINCT p.ID) AS cnt
			FROM {$wpdb->term_relationships} tr
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'post_tag'
			INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = %s AND p.post_status = 'publish'
			GROUP BY t.term_id, t.slug, t.name
			HAVING cnt > 0
			ORDER BY t.name ASC",
			$pt
		),
		ARRAY_A
	);
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return array();
	}
	$out = array();
	foreach ( $rows as $row ) {
		if ( empty( $row['slug'] ) ) {
			continue;
		}
		$out[ $row['slug'] ] = array(
			'name'  => $row['name'],
			'count' => (int) $row['cnt'],
		);
	}
	return $out;
}

/**
 * Parametri catalogo da array GET/POST (chiavi opendata_*).
 *
 * @param array $src $_GET o $_POST.
 * @return array{search:string,org_id:int,group_slug:string,order_key:string,paged:int,formats:string[],tags:string[]}
 */
function italiawp2_opendata_catalog_parse_params( $src ) {
	$search = isset( $src['opendata_s'] ) ? sanitize_text_field( wp_unslash( $src['opendata_s'] ) ) : '';
	$org_id = isset( $src['opendata_org'] ) ? max( 0, (int) $src['opendata_org'] ) : 0;
	$group_slug = isset( $src['opendata_group'] ) ? sanitize_text_field( wp_unslash( $src['opendata_group'] ) ) : '';
	$order_key = isset( $src['opendata_order'] ) ? sanitize_text_field( wp_unslash( $src['opendata_order'] ) ) : 'modified';
	$paged = isset( $src['opendata_paged'] ) ? max( 1, (int) $src['opendata_paged'] ) : 1;

	$formats = array();
	if ( isset( $src['opendata_formats'] ) ) {
		if ( is_array( $src['opendata_formats'] ) ) {
			foreach ( $src['opendata_formats'] as $f ) {
				$f = strtolower( sanitize_key( (string) $f ) );
				if ( $f !== '' ) {
					$formats[] = $f;
				}
			}
		} else {
			$raw = sanitize_text_field( wp_unslash( (string) $src['opendata_formats'] ) );
			foreach ( explode( ',', $raw ) as $part ) {
				$f = strtolower( sanitize_key( trim( $part ) ) );
				if ( $f !== '' ) {
					$formats[] = $f;
				}
			}
		}
		$formats = array_unique( $formats );
	}

	$tags = array();
	if ( isset( $src['opendata_tags'] ) ) {
		if ( is_array( $src['opendata_tags'] ) ) {
			foreach ( $src['opendata_tags'] as $t ) {
				$t = sanitize_title( (string) $t );
				if ( $t !== '' ) {
					$tags[] = $t;
				}
			}
		} else {
			$raw = sanitize_text_field( wp_unslash( (string) $src['opendata_tags'] ) );
			foreach ( explode( ',', $raw ) as $part ) {
				$t = sanitize_title( trim( $part ) );
				if ( $t !== '' ) {
					$tags[] = $t;
				}
			}
		}
		$tags = array_unique( $tags );
	}

	return array(
		'search'      => $search,
		'org_id'      => $org_id,
		'group_slug'  => $group_slug,
		'order_key'   => $order_key,
		'paged'       => $paged,
		'formats'     => $formats,
		'tags'        => $tags,
	);
}

/**
 * Argomenti per WP_Query catalogo dataset.
 *
 * @param array $p Output di italiawp2_opendata_catalog_parse_params().
 * @return array
 */
function italiawp2_opendata_catalog_query_args( $p ) {
	$allowed_order = array(
		'modified' => 'date',
		'title'    => 'title',
		'date'     => 'date',
	);
	$orderby = isset( $allowed_order[ $p['order_key'] ] ) ? $allowed_order[ $p['order_key'] ] : 'date';
	$per_page = (int) apply_filters( 'opendata_catalog_per_page', 10 );

	$args = array(
		'post_type'      => OpenData_Pa_Dataset::POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => (int) $p['paged'],
		'orderby'        => $orderby,
		'order'          => ( $orderby === 'title' ) ? 'ASC' : 'DESC',
	);

	if ( $p['search'] !== '' ) {
		$args['s'] = $p['search'];
	}
	if ( $p['org_id'] > 0 ) {
		$args['meta_query'] = array(
			array(
				'key'     => OpenData_Pa_Dataset::META_ORGANIZATION_ID,
				'value'   => (int) $p['org_id'],
				'compare' => '=',
			),
		);
	}
	$tax_q = array();
	if ( $p['group_slug'] !== '' && class_exists( 'OpenData_Pa_Groups' ) && taxonomy_exists( OpenData_Pa_Groups::TAXONOMY ) ) {
		$tax_q[] = array(
			'taxonomy' => OpenData_Pa_Groups::TAXONOMY,
			'field'    => 'slug',
			'terms'    => $p['group_slug'],
		);
	}
	$tag_slugs = isset( $p['tags'] ) ? $p['tags'] : array();
	if ( ! empty( $tag_slugs ) && taxonomy_exists( 'post_tag' ) ) {
		$tag_slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', (array) $tag_slugs ) ) ) );
		if ( ! empty( $tag_slugs ) ) {
			$tax_q[] = array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $tag_slugs,
				'operator' => 'IN',
			);
		}
	}
	if ( count( $tax_q ) > 1 ) {
		$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_q );
	} elseif ( count( $tax_q ) === 1 ) {
		$args['tax_query'] = $tax_q;
	}

	if ( ! empty( $p['formats'] ) && class_exists( 'OpenData_Pa_Resource' ) ) {
		$format_ids = italiawp2_opendata_catalog_dataset_ids_for_formats( $p['formats'] );
		if ( empty( $format_ids ) ) {
			$args['post__in'] = array( 0 );
		} else {
			$args['post__in'] = $format_ids;
		}
	}

	return $args;
}

/**
 * HTML risultati (conteggio, lista card, paginazione o messaggio vuoto).
 *
 * @param WP_Query $query Query eseguita.
 * @param array    $p     Parametri per paginazione.
 * @return string HTML (senza wrapper colonna).
 */
function italiawp2_opendata_catalog_results_html( $query, $p ) {
	if ( ! $query instanceof WP_Query ) {
		return '';
	}

	$total  = (int) $query->found_posts;
	$paged  = (int) $p['paged'];
	$search = $p['search'];
	$org_id = (int) $p['org_id'];
	$group_slug = $p['group_slug'];
	$formats    = isset( $p['formats'] ) ? $p['formats'] : array();
	$tags_pag   = isset( $p['tags'] ) ? $p['tags'] : array();
	$order_for_pagination = $p['order_key'] !== '' ? $p['order_key'] : 'modified';

	ob_start();
	?>
	<p class="opendata-catalog-count text-white mb-3">
		<?php
		printf(
			esc_html__( 'Dataset trovati: %d', 'italiawp2' ),
			$total
		);
		?>
	</p>

	<?php if ( $query->have_posts() ) : ?>
		<div class="opendata-catalog-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$dataset_id = get_the_ID();
				$org_id_d   = OpenData_Pa_Dataset::get_organization_id( $dataset_id );
				$org_name   = $org_id_d ? OpenData_Pa_Organization::get_name( $org_id_d ) : '';
				$excerpt    = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 25 );
				$format_badges = italiawp2_opendata_catalog_dataset_format_badges( $dataset_id );
				$terms_list = array();
				if ( class_exists( 'OpenData_Pa_Groups' ) && taxonomy_exists( OpenData_Pa_Groups::TAXONOMY ) ) {
					$terms_list = get_the_terms( $dataset_id, OpenData_Pa_Groups::TAXONOMY );
					if ( is_wp_error( $terms_list ) || ! is_array( $terms_list ) ) {
						$terms_list = array();
					}
				}
				?>
				<article class="opendata-catalog-item card card-wrapper border-0 shadow-sm mb-3">
					<div class="card-body">
						<h3 class="h5 card-title mb-2">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>
						<?php if ( $excerpt ) : ?>
							<p class="card-text text-muted small mb-2"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<?php if ( $org_name ) : ?>
							<p class="small mb-1"><?php esc_html_e( 'Pubblicato da:', 'italiawp2' ); ?> <?php echo esc_html( $org_name ); ?></p>
						<?php endif; ?>
						<p class="small text-muted mb-2"><?php esc_html_e( 'Data di ultima modifica:', 'italiawp2' ); ?> <?php echo esc_html( get_the_modified_date( 'Y-m-d' ) ); ?></p>
						<?php if ( ! empty( $format_badges ) ) : ?>
							<div class="opendata-catalog-format-badges mb-2" aria-label="<?php esc_attr_e( 'Formati delle distribuzioni', 'italiawp2' ); ?>">
								<?php foreach ( $format_badges as $fb ) : ?>
									<span class="badge rounded-pill bg-success text-white opendata-format-badge"><?php echo esc_html( $fb ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $terms_list ) ) : ?>
							<div class="opendata-catalog-tags">
								<?php foreach ( $terms_list as $t ) : ?>
									<a href="<?php echo esc_url( get_term_link( $t ) ); ?>" class="btn btn-outline-primary btn-sm me-1 mb-1"><?php echo esc_html( $t->name ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		$total_pages = (int) $query->max_num_pages;
		if ( $total_pages > 1 ) :
			$pagination_params = array(
				'opendata_s'     => $search,
				'opendata_org'   => $org_id,
				'opendata_group' => $group_slug,
				'opendata_order' => $order_for_pagination,
				'opendata_paged' => '%#%',
			);
			if ( ! empty( $formats ) ) {
				$pagination_params['opendata_formats'] = implode( ',', $formats );
			}
			if ( ! empty( $tags_pag ) ) {
				$pagination_params['opendata_tags'] = implode( ',', $tags_pag );
			}
			$base = add_query_arg( $pagination_params, home_url( '/' ) );
			?>
			<nav class="opendata-catalog-pagination mt-4" aria-label="<?php esc_attr_e( 'Paginazione dataset', 'italiawp2' ); ?>">
				<ul class="pagination justify-content-center">
					<?php if ( $paged > 1 ) : ?>
						<li class="page-item">
							<a class="page-link" href="<?php echo esc_url( str_replace( '%#%', (string) ( $paged - 1 ), $base ) ); ?>">&lt;</a>
						</li>
					<?php endif; ?>
					<?php
					$start = max( 1, $paged - 2 );
					$end   = min( $total_pages, $paged + 2 );
					for ( $i = $start; $i <= $end; $i++ ) :
						$page_url = str_replace( '%#%', (string) $i, $base );
						?>
						<li class="page-item <?php echo (int) $i === $paged ? 'active' : ''; ?>">
							<a class="page-link" href="<?php echo esc_url( $page_url ); ?>"><?php echo (int) $i; ?></a>
						</li>
					<?php endfor; ?>
					<?php if ( $paged < $total_pages ) : ?>
						<li class="page-item">
							<a class="page-link" href="<?php echo esc_url( str_replace( '%#%', (string) ( $paged + 1 ), $base ) ); ?>">&gt;</a>
						</li>
					<?php endif; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<?php
		wp_reset_postdata();
	else :
		?>
		<p class="alert alert-info text-white"><?php esc_html_e( 'Nessun dataset trovato. Modifica i filtri o effettua una ricerca.', 'italiawp2' ); ?></p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * AJAX: restituisce HTML risultati catalogo.
 * 
 * @return void
 */
function italiawp2_ajax_catalog_datasets() {
	check_ajax_referer( 'italiawp2_catalog', 'nonce' );

	if ( ! class_exists( 'OpenData_Pa_Dataset' ) || ! class_exists( 'OpenData_Pa_Organization' ) ) {
		wp_send_json_error( array( 'message' => 'Open Data PA non attivo.' ), 400 );
	}

	$p = italiawp2_opendata_catalog_parse_params( $_POST );
	$p['paged'] = 1;

	$args  = italiawp2_opendata_catalog_query_args( $p );
	$query = new WP_Query( $args );

	wp_send_json_success(
		array(
			'html' => italiawp2_opendata_catalog_results_html( $query, $p ),
		)
	);
}
add_action( 'wp_ajax_italiawp2_catalog_datasets', 'italiawp2_ajax_catalog_datasets' );
add_action( 'wp_ajax_nopriv_italiawp2_catalog_datasets', 'italiawp2_ajax_catalog_datasets' );

/**
 * Script e stili catalogo (filtri AJAX).
 *
 * @return void
 */
function italiawp2_enqueue_opendata_catalog_assets() {
	if ( ! is_front_page() || ! get_theme_mod( 'active_section_dataset_catalog', true ) ) {
		return;
	}
	if ( ! class_exists( 'OpenData_Pa_Dataset' ) ) {
		return;
	}

	wp_enqueue_script(
		'italiawp2-opendata-catalog',
		get_template_directory_uri() . '/inc/opendata-catalog.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
	wp_localize_script(
		'italiawp2-opendata-catalog',
		'italiawp2Catalog',
		array(
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'italiawp2_catalog' ),
			'loadingLabel'   => __( 'Caricamento risultati', 'italiawp2' ),
			'searchDebounce' => 450,
		)
	);

	wp_add_inline_style(
		'italiawp2-style',
		'#opendata-catalog-results{position:relative;min-height:12rem}' .
		'#opendata-catalog-results .opendata-catalog-loader-overlay{position:absolute;inset:0;z-index:2;display:none;align-items:center;justify-content:center;background:rgba(0,35,77,.45);border-radius:4px}' .
		'#opendata-catalog-results.is-loading .opendata-catalog-loader-overlay{display:flex}' .
		'#opendata-catalog-results.is-loading .opendata-catalog-results-inner{pointer-events:none;opacity:.35}'
	);
}
add_action( 'wp_enqueue_scripts', 'italiawp2_enqueue_opendata_catalog_assets', 25 );
