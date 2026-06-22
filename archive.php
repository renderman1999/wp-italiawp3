<?php

get_header();

if ( function_exists( 'italiawp2_is_opendata_dataset_search' ) && italiawp2_is_opendata_dataset_search() ) {
	get_template_part( 'template-parts/dataset-search-loop' );
} else {
	get_template_part( 'template-parts/archive-loop' );
}

get_sidebar();
get_footer();
