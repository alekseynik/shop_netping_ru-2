<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Homepage V1
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			
			<div class="homepage_banner">
				<?php the_content(); ?>
			</div>
			
			<div class="homepage-products-tabs">
				<div class="tab-row">
					<a href="#featured" data-tab="featured" class="p-nav-tab active"><span>Рекомендуем</span></a>
					<a href="#new" data-tab="new" class="p-nav-tab"><span>Новинки</span></a>
					<a href="#sale" data-tab="sale" class="p-nav-tab"><span>Акции</span></a>
				</div>
				<div class="products-grid">
					<div id="featured" class="p-tab active">
						<?php echo do_shortcode('[products limit="6" columns="3" visibility="featured"]') ?>
					</div>
					<div id="new" class="p-tab">
						<?php echo do_shortcode('[products limit="6" columns="3" orderby="date"]') ?>
					</div>
					<div id="sale" class="p-tab">
						<?php echo do_shortcode('[products limit="6" columns="3" on_sale="true"]') ?>
					</div>
				</div>
			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
do_action( 'storefront_sidebar' );
get_footer();
