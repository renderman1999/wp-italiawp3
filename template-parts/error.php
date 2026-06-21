<?php
/*
 * ### ERRORE / NESSUN CONTENUTO ###
 *
 */
?>

<div class="col-12 linetop pt8">
	<div class="articolo-paragrafi">
		<div class="row">
			<div class="col-12">
				<div class="card card-wrapper border shadow-sm mb-4 bg-white">
					<div class="card-body p-4">
						<div class="alert alert-info mb-0" role="alert">
							<h2 class="h5 alert-heading mb-3"><?php echo esc_html( __( 'Error', 'italiawp2' ) ); ?></h2>
							<p class="mb-3">
								<?php echo esc_html( __( 'There is no content', 'italiawp2' ) ); ?>,
								<a href="javascript:history.back();" class="alert-link"><?php echo esc_html( __( 'come back', 'italiawp2' ) ); ?></a>
								<?php echo ' ' . esc_html( __( 'or use the menu to continue browsing', 'italiawp2' ) ); ?>.
							</p>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-sm text-white"><?php echo esc_html( __( 'Back to Home', 'italiawp2' ) ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
