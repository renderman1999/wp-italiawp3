<?php
/*
 * ### LOOP PRINCIPALE ###
 *
 */
?>

<section id="sezione-notizie" class="pt64 pb32 bg-grigio">
    <div class="container">

<?php
    $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    $total_post = wp_count_posts();
    $published_post = $total_post->publish;
    $total_pages = ceil( $published_post / $posts_per_page ); ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="titolosezione">
                    <h3><?php the_archive_title('',''); ?>
                        <?php if($wp_query->max_num_pages != 0) { ?>
                        <br><small><?php echo __('Page','italiawp2'); ?> <?php echo $paged; ?> <?php echo __('of','italiawp2'); ?> <?php echo $wp_query->max_num_pages; ?></small>
                        <?php } ?>
                    </h3>
                </div>
            </div>
        </div>
		<?php
		$is_dataset_archive = is_post_type_archive( 'opendata_dataset' );
		if ( $is_dataset_archive ) :
			$search_value = get_search_query();
			?>
		<div class="row mb-3">
			<div class="col-md-12">
				<form role="search" method="get" class="opendata-archive-search-form" action="<?php echo esc_url( get_post_type_archive_link( 'opendata_dataset' ) ); ?>">
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
        <div class="row row-eq-height">

<?php

$i = 0; if (have_posts()) :
    while (have_posts()) : the_post();

    $i++;

    $img_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'news-image' );
    if($img_url!="") {
        $img_url = $img_url[0];
    }else if(get_theme_mod('active_immagine_evidenza_default')) {
        $img_url = esc_url(get_theme_mod('immagine_evidenza_default'));
        if($img_url=="") {
            $img_url = esc_url( get_template_directory_uri() ) . "/images/400x220.png";
        }
    }
    
    $category = get_the_category();
    $posttags = get_the_tags();
    $datapost = get_the_date('j F Y', '', ''); ?>
                    
            <div class="col-md-4 mb32-l">

                <article class="scheda scheda-round scheda-news card">
                    
                    <?php if($img_url!="") { ?>
                    <div class="scheda-foto">
                        <a href="<?php the_permalink(); ?>">
                            <figure>
                                <img src="<?php print $img_url; ?>" alt="<?php the_title(); ?>" class="img-fluid" />
                            </figure>
                        </a>
                        
                    <?php $dataevento = get_post_meta($post->ID, 'Data', true);
                        if ($dataevento) {
                            $dataevento = explode(" ",$dataevento); ?>
                        <div class="card-calendar d-flex flex-column justify-content-center">
                          <span class="card-date"><?php echo $dataevento[0]; ?></span>
                          <span class="card-day"><?php echo $dataevento[1].'<br>'.$dataevento[2]; ?></span>
                        </div>
                    <?php } ?>
                        
                    </div>
                    <?php } ?>
                    
                <?php if ( 'post' == get_post_type( $post->ID ) ) : ?>
                    <div class="scheda-icona-small">
                    <?php if(is_sticky( $post->ID )) { ?>
                        <div class="flag-icon"></div>
                    <?php } ?>

                        <svg class="icon"><use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/img/ponmetroca.svg#ca-today"></use></svg>
                        <?php echo $datapost; ?>
                    </div>
                <?php elseif ( 'page' == get_post_type( $post->ID ) ) : ?>
                    <div class="scheda-icona-small">
                        <svg class="icon"><use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/img/ponmetroca.svg#ca-account_balance"></use></svg>
                        <?php echo __('Page','italiawp2'); ?>
                    </div>
                <?php else: ?>
                    <div class="scheda-icona-small">
                        <svg class="icon"><use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/img/ponmetroca.svg#ca-account_balance"></use></svg>
                        <?php echo get_post_type_object(get_post_type( $post->ID ))->labels->singular_name; ?>
                    </div>
                <?php endif; ?>
                    
                    <div class="scheda-testo <?php if($img_url=="") echo 'scheda-testo-nofoto'; ?>">
                        
                        <h4>
                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h4>
                        <?php
                        $is_opendata_dataset = ( class_exists( 'OpenData_Pa_Dataset' ) && get_post_type( $post->ID ) === OpenData_Pa_Dataset::POST_TYPE )
                            || get_post_type( $post->ID ) === 'opendata_dataset';
                        if ( $is_opendata_dataset ) {
                            $body = get_post_field( 'post_content', $post->ID );
                            $body = apply_filters( 'the_content', $body );
                            $plain = wp_strip_all_tags( $body );
                            $limit = (int) apply_filters( 'italiawp2_dataset_archive_excerpt_chars', 250 );
                            if ( $limit < 1 ) {
                                $limit = 250;
                            }
                            if ( mb_strlen( $plain ) <= $limit ) {
                                echo '<div class="scheda-testo-excerpt scheda-dataset-excerpt">' . wp_kses_post( $body ) . '</div>';
                            } else {
                                $short = mb_substr( $plain, 0, $limit ) . '...';
                                echo '<div class="scheda-testo-excerpt scheda-dataset-excerpt">' . wp_kses_post( wpautop( esc_html( $short ) ) ) . '</div>';
                            }
                        } else {
                            ?>
                        <p><?php echo get_the_excerpt(); ?></p>
                        <?php
                        }
                        ?>
                    </div>
                    
                    <?php if (!empty($category)) { ?>
                    <div class="scheda-argomenti">
                        <h4><?php echo __('Categories','italiawp2'); ?></h4>
                        <?php
                            foreach ($category as $cat) {
                                echo '<a href="' . esc_url(get_category_link($cat->term_id)) . '" title="' . esc_html($cat->name) . '" class="badge badge-pill badge-argomenti">' . esc_html($cat->name) . '</a>';
                            }
                         ?>
                    </div>
                    <?php } ?>
                    
                    <?php if (!empty($posttags)) { ?>
                    <div class="scheda-argomenti">
                        <h4><?php echo __('Topics','italiawp2'); ?></h4>
                        <div class="argomenti-sezione-elenco">
                        <?php
                            foreach ($posttags as $tag) {
                                echo '<a href="' . esc_url(get_tag_link($tag)) . '" title="' . esc_html($tag->name) . '" class="badge badge-pill badge-argomenti">' . esc_html($tag->name) . '</a>';
                            }
                         ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="scheda-footer">
                        <a href="<?php the_permalink(); ?>" title="<?php echo __('Go to the page','italiawp2'); ?>: <?php the_title(); ?>" class="tutte">
                            <?php echo __('Read more','italiawp2'); ?>
                            <svg class="icon">
                                <use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/static/img/ponmetroca.svg#ca-arrow_forward"></use>
                            </svg>
                        </a>
                    </div>
                </article>
            </div>

<?php endwhile;
      else : get_template_part('template-parts/error');
      endif; ?>

        </div>
    </div>
</section>

<?php get_template_part('template-parts/pagination');
