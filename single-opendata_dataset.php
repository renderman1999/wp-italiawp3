<?php
/**
 * Template per la pagina singola Dataset (Open Data PA).
 *
 * Copyright (c) 2025-2026 Asymmetrica (opendata@asymmetrica.it).
 * Part of ItaliaWP3 — derivative of ItaliaWP2 (Boris Amico), GPL-3.0 or later.
 */
get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', 'dataset-detail' );
	endwhile;
endif;

get_template_part( 'template-parts/section-single-last-news' );
get_sidebar();
get_footer();
