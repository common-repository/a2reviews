<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="a2-content-area">
		<main id="main" class="site-main" role="main">
			<div class="a2-feature-reviews">
				<div class="a2reviews-wrapper">
					<h1>Feature Reviews</h1>
					
					<?php do_action( 'before_a2_feature_reviews' ); ?>
					
				    <a2-feature-reviews 
				        lang="en">
				        A2 Feature Reviews Widget
				    </a2-feature-reviews>
				    
				    <?php do_action( 'after_a2_feature_reviews' ); ?>
				</div>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
	
get_footer();