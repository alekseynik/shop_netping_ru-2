<?php
/**
 * Single variation display
 *
 * This is a javascript-based template for single variations (see https://codex.wordpress.org/Javascript_Reference/wp.template).
 * The values will be dynamically replaced after selecting attributes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

?>
<script type="text/template" id="tmpl-variation-template">
	<div class="woocommerce-variation-title"><h2>{{{ data.variation.product_title }}}</h2></div>
	<div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
	<div class="woocommerce-variation-desc-link"><a href="#tab-title-description" class="desc-tab-link">Описание</a></div>
	<div class="woocommerce-variation-eol">{{{ data.variation.eol_html }}}</div>
	<div class="woocommerce-variation-price">{{{ data.variation.price_html }}}</div>
	<div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
</script>
<script type="text/template" id="tmpl-unavailable-variation-template">
	<p><?php esc_html_e( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ); ?></p>
</script>
