<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
do_action( 'woocommerce_cart_is_empty' );

// global $wp;
// echo '<pre>';
// var_dump( $wp );
// echo is_wc_endpoint_url( 'order-received' );
// echo $wp->request;

?>
<div class="empty-cart-wrapper">
	<img src="<?php echo get_stylesheet_directory_uri() ?>/assets/icons/empty_cart.png" style='margin-bottom:30px;' alt="Корзина пуста">
	<p>В корзине нет товаров</p>

	<?php if ( wc_get_page_id( 'shop' ) > 0 && !is_wc_endpoint_url( 'order-received') ) : ?>
		<p class="return-to-shop">
			<a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
				<?php
					/**
					 * Filter "Return To Shop" text.
					 *
					 * @since 4.6.0
					 * @param string $default_text Default text.
					 */
					echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ) );
				?>
			</a>
		</p>
	<?php endif; ?>
	
		<div class="consultations-form">
			<?php if ( !is_wc_endpoint_url( 'order-received' ) ) : ?>
				<p>Вы можете обратиться к нашим менеджерам для консультации</p>
			
				<a href="#open_consultations_modal" class="button">Оставить заявку</a>
				<div id="open_consultations_modal" class="modalDialog" tabindex="-1">
					<a href="#close" class="background_close"></a>
					<div>
						<a href="#close" title="Закрыть" class="close">&times;</a>
						<h3>Консультация менеджера</h3>
						<?php echo do_shortcode('[contact-form-7 id="206" title="Консультация менеджера"]'); ?>
					</div>
				</div>
			<?php else: ?>
				<p>Спасибо за ваш заказ.</br> Мы свяжемся с вами  при первой возможности  для подтверждения заказа.</p>
				<a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">Понятно</a>
			<?php endif; ?>
		</div>
		
		<?php if ( is_wc_endpoint_url( 'order-received' ) ) : ?>

		<?php endif; ?>


</div>

<?php add_featured_products_section() ?>
