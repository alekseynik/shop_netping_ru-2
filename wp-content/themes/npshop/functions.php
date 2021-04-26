<?php
/**
 * NetPing shop theme Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package npshop
 */



//SECTION General setup

//ANCHOR load scripts
add_action('wp_enqueue_scripts', 'npshop_frontend_scripts');
function npshop_frontend_scripts() {
	if ( is_checkout() ) {
		wp_enqueue_script( 'checkoutscripts', get_stylesheet_directory_uri() . '/js/checkoutscripts.js', array( 'jquery' ));
		wp_localize_script( 'checkoutscripts', 'custom_values', array(
			'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'token'     => wp_create_nonce( 'token' )
		));
		wp_enqueue_script('ya_share', 'https://yastatic.net/share2/share.js', array() );
	}
}

//ANCHOR add micro_thumb image size
add_image_size('micro_thumb', 80, 80, true);

//ANCHOR change storefront default woocommerce params (image sizes)
add_filter('storefront_woocommerce_args', 'npshop_change_strorefront_wc_settings');
function npshop_change_strorefront_wc_settings() {
	return array(
		'single_image_width'    => 530,
		'thumbnail_image_width' => 324,
		'gallery_thumbnail_image_width' => 140,
		'product_grid'          => array(
			'default_columns' => 3,
			'default_rows'    => 4,
			'min_columns'     => 1,
			'max_columns'     => 6,
			'min_rows'        => 1,
		)
	);
}

//SECTION Meta metabox with all meta fields for admin (based on woocmmerce-jetpack plugin script) 

//ANCHOR create metabox
function create_meta_meta_box( $post ) {
	$html    = '';
	$post_id = get_the_ID();
	$product = wc_get_product( $post->ID );

	$meta = get_post_meta( $post_id );
	$table_data = array();
	foreach ( $meta as $meta_key => $meta_values ) {
		$table_data[] = array( $meta_key, esc_html( print_r( maybe_unserialize( $meta_values[0] ), true ) ) );
	}
	$html .= get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'vertical' ) );

	//variations
	if ( 'product' === $post->post_type && $product->is_type( 'variable' ) ) {
		$variations = $product->get_available_variations();
		foreach ( $variations as $variable_array ) {
			$variation_id = $variable_array['variation_id'];
			$meta = get_post_meta( $variation_id );
			$table_data = array();
			$table_data[] = array('<span style="font-weight:bold;">Вариация: '.$variation_id.'</span>','');
			foreach ( $meta as $meta_key => $meta_values ) {
				$table_data[] = array( $meta_key, esc_html( print_r( maybe_unserialize( $meta_values[0] ), true ) ) );
			}
			$html .= get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'vertical' ) );
		}
	}

	// items meta (for orders only)
	if ( 'shop_order' === $post->post_type ) {
		$_order = wc_get_order( $post_id );
		$table_data = array();
		foreach ( $_order->get_items() as $item_key => $item ) {
			foreach ( $item['item_meta'] as $item_meta_key => $item_meta_value ) {
				$table_data[] = array( $item_key, $item_meta_key, esc_html( print_r( maybe_unserialize( $item_meta_value ), true ) ) );
			}
		}
		if ( ! empty( $table_data ) ) {
			$html .= '<h3>Order Items Meta</h3>';
			$table_data = array_merge(
				array( array( 'Item Key', 'Item Meta Key', 'Item Meta Value' ) ),
				$table_data
			);
			$html .= get_table_html( $table_data, array( 'table_class' => 'widefat striped', 'table_heading_type' => 'horizontal' ) );
		}
	}
	echo $html;
}

//ANCHOR generate table
if ( ! function_exists( 'get_table_html' ) ) {
	function get_table_html( $data, $args = array() ) {
		$defaults = array(
			'table_class'        => '',
			'table_style'        => '',
			'row_styles'         => '',
			'table_heading_type' => 'horizontal',
			'columns_classes'    => array(),
			'columns_styles'     => array(),
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		$table_class = ( '' == $table_class ) ? '' : ' class="' . $table_class . '"';
		$table_style = ( '' == $table_style ) ? '' : ' style="' . $table_style . '"';
		$row_styles  = ( '' == $row_styles )  ? '' : ' style="' . $row_styles  . '"';
		$html = '';
		$html .= '<table' . $table_class . $table_style . '>';
		$html .= '<tbody>';
		foreach( $data as $row_number => $row ) {
			$html .= '<tr' . $row_styles . '">';
			foreach( $row as $column_number => $value ) {
				$th_or_td = ( ( 0 === $row_number && 'horizontal' === $table_heading_type ) || ( 0 === $column_number && 'vertical' === $table_heading_type ) ) ? 'th' : 'td';
				$column_class = ( ! empty( $columns_classes ) && isset( $columns_classes[ $column_number ] ) ) ? ' class="' . $columns_classes[ $column_number ] . '"' : '';
				$column_style = ( ! empty( $columns_styles ) && isset( $columns_styles[ $column_number ] ) ) ? ' style="' . $columns_styles[ $column_number ] . '"' : '';

				$html .= '<' . $th_or_td . $column_class . $column_style . '>';
				$html .= $value;
				$html .= '</' . $th_or_td . '>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
}

//ANCHOR add metabox
add_action( 'add_meta_boxes', 'add_order_meta_meta_box' );
function add_order_meta_meta_box() {
	add_meta_box(
		'products-and-orders-meta-table',
		'Все мета поля:',
		'create_meta_meta_box',
		array('shop_order','product', 'user'),
		'normal',
		'low'
	);
}

//!SECTION Meta metabox with all meta fields for admin (based on woocmmerce-jetpack plugin script)

//SECTION Prices formatting

//ANCHOR remove price range for product on category pages
add_filter('woocommerce_variable_price_html', 'variable_price_at_category', 10, 2);
function variable_price_at_category($price, $product) {
	global $woocommerce_loop;
	global $post;
	if ( is_product_category() || is_shop() || is_front_page() || is_cart() || has_shortcode( get_the_content(), 'products' ) || $woocommerce_loop['name'] == 'up-sells' ) {
		$prices = $product->get_variation_prices( true );
		$min_price = current( $prices['price'] );

		$price = wc_price( $min_price ) . $product->get_price_suffix();
	
		// $min_price_html = wc_price( $min_price ) . $product->get_price_suffix();
		// $price = sprintf( __( 'От %1$s', 'woocommerce' ), $min_price_html );
	}
	return $price;
}

//ANCHOR Format Sale prices: remove on categories, add акция! and wrappers on single product
add_filter( 'woocommerce_format_sale_price', 'remove_sale_price_on_category', 10, 3 );
function remove_sale_price_on_category( $price, $regular_price, $sale_price ) {
	global $woocommerce_loop;
	if ( is_product_category() || is_shop() || is_front_page() || has_shortcode( get_the_content(), 'products' ) || $woocommerce_loop['name'] == 'up-sells' ) {
    	$price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins>';
	} elseif ( is_cart() ) {
		$price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price )  : $sale_price ) . '</ins> <del>' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
	} else {
		$price = '<ins class="price_wrap">' . ( is_numeric( $sale_price ) ? wc_price( $sale_price )  : $sale_price ) . '<span class="nds_notice"><span>НДС: включено в цену</span><span>Доставка: по России бесплатно</span></span></ins> <del class="price_wrap">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '<span class="action">Акция!</span></del>';
	}
	return $price;
}

// add_filter('woocommerce_format_sale_price', 'custom_variable_sale_price_format', 10, 3);
function custom_variable_sale_price_format($price, $regular_price, $sale_price) {
	global $product;
	global $woocommerce_loop;
	if ( !is_admin() ) {
		if ( is_product() && !$woocommerce_loop ?: !$woocommerce_loop['name'] == 'up-sells' ) {
			$price = '<ins class="price_wrap">' . ( is_numeric( $sale_price ) ? wc_price( $sale_price )  : $sale_price ) . '</ins> <del class="price_wrap">' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '<div class="action">Акция!</div></del>';
		}
	}
	return $price;
}

//ANCHOR add НДС... to price html on single product
// add_filter('wc_price', 'add_price_notice', 10, 5);
function add_price_notice($return, $price, $args, $unformatted_price, $original_price ) {
	global $product;
	global $woocommerce_loop;
	// if ( is_product() && $product->is_type('variable') ) {
	if ( is_product() && $product->is_type('variable') && !$woocommerce_loop['name'] == 'up-sells' ) {
		$return .= '<div class="nds_notice"><span>НДС: включено в цену</span><span>Доставка: по России бесплатно</span></div>';
	}
	
	if ( is_product() && $product->is_type('simple') && !$woocommerce_loop['name'] == 'up-sells' ) {
		$return .=  '<span class="nds_notice"><span>НДС: включено в цену</span><span>Доставка: по России бесплатно</span></span>';
	}

	return $return;
}


add_action('storefront_after_footer', 'test_output');
function test_output() {
	// echo is_product();
	// $page_id = get_queried_object_id();
	// $page_object = get_page( $page_id );
	// if ( strpos($page_object->post_content, '[/slider]') );
	// 	echo '<pre>';
	// var_dump (get_post_meta($post->ID));
}


//!SECTION Prices formatting

// ANCHOR Rename 'posts' to 'Новости'

add_action( 'admin_menu', 'npshop_edit_admin_menus' );
add_action( 'init', 'npshop_change_post_object' );
 
function npshop_edit_admin_menus() {
    global $menu;
    global $submenu;
 
    $menu[5][0] = 'Новости'; // Change Posts to Houses
    $submenu['edit.php'][5][0] = 'Новости';
    $submenu['edit.php'][10][0] = 'Добавить новость';
    $submenu['edit.php'][16][0] = 'Теги новостей';
}
 
function npshop_change_post_object() {
    global $wp_post_types;
 
    $labels = &$wp_post_types['post']->labels;
 
    $labels->name = 'Новости';
    $labels->singular_name = 'Новость';
    $labels->add_new = 'Добавить новость';
    $labels->add_new_item = 'Добавить новость';
    $labels->edit_item = 'Редактировать новость';
    $labels->new_item = 'Новость';
    $labels->view_item = 'Смотреть новость';
    $labels->search_items = 'Искать в новостях';
    $labels->not_found = 'Не найдено новостей';
    $labels->not_found_in_trash = 'В корзине не найдено новостей';
    $labels->all_items = 'Все новости';
    $labels->menu_name = 'Новости';
    $labels->name_admin_bar = 'Новости';

	$wp_post_types['post']->menu_icon = 'dashicons-media-document';	
}

//ANCHOR Change a currency symbol
add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'RUB': $currency_symbol = 'руб.'; 
		  break;
     }
     return $currency_symbol;
}

//ANCHOR add/remove body classes
add_filter( 'body_class', 'add_remove_body_classes', 20, 2);
function add_remove_body_classes( $classes, $extra_classes ) {
	
	if ( is_product() || is_cart() ) {

		$key = array_search( 'left-sidebar', $classes, true );

		if ( false !== $key ) {
			unset( $classes[ $key ] );
		}
	}
	
	return $classes;
}


//ANCHOR add qty minus buttoun
add_action('woocommerce_before_quantity_input_field', 'echo_qty_minus');
function echo_qty_minus() {
	echo '<input type="button" value="-" class="qty_button minus" />';
}

//ANCHOR add qty plus buttoun
add_action('woocommerce_after_quantity_input_field', 'echo_qty_plus');
function echo_qty_plus() {
	echo '<input type="button" value="+" class="qty_button plus" />';
}

//ANCHOR change woocommerce breadcrumbs delimeter & add cart share button on cart page
add_filter( 'woocommerce_breadcrumb_defaults', 'npshop_woocommerce_breadcrumbs', 20 );
function npshop_woocommerce_breadcrumbs($defaults) {
    $defaults['delimiter'] = '<span class="delim"> / </span>';
	if ( is_cart() && WC()->cart->get_cart_contents_count() !== 0 ) {
		$defaults['wrap_after']  = '</nav><div class="share-cart">' . cart_ya_share_link_html() . '</div></div></div>';
		// $defaults['wrap_after']  = '</nav><div class="share-cart">' . 'поделиться корзиной' . '</div></div></div>';
	}
	return $defaults;
}

//ANCHOR add "Каталог" Breadcrumb
add_filter( 'woocommerce_get_breadcrumb', function($crumbs, $Breadcrumb) {
	$shop_page_id = wc_get_page_id('shop');
	if( wc_get_page_id('shop') > 0 && !is_shop() && !is_cart() ) {
		$new_breadcrumb = [
			'Каталог', 
			get_permalink(wc_get_page_id('shop'))
		];
		array_splice($crumbs, 1, 0, [$new_breadcrumb]);
	}
	return $crumbs;
}, 10, 2 );

//ANCHOR remove autop in CF7
add_action( 'wpcf7_autop_or_not', '__return_false' );

add_filter('woocommerce_variable_price_html', 'custom_variation_price', 10, 2);
function custom_variation_price( $price, $product ) {
    if ( ! empty($product->min_variation_price) && is_product_category() ) {
        $price = '<span class="from">' . _x('From', 'min_price', 'woocommerce') . ' </span>';
        $price .= wc_price($product->get_price());
    }

    return $price;
}


//!SECTION General setup

//SECTION HEADER 

//ANCHOR rearrange header elements
add_action('init', 'npshop_rearrange_header_elements');
function npshop_rearrange_header_elements() {
	remove_action('storefront_header', 'storefront_social_icons', 10); //removed
	remove_action('storefront_header', 'storefront_site_branding', 20); //priority changed to 50
	remove_action('storefront_header', 'storefront_secondary_navigation', 30); //removed
	remove_action('storefront_header', 'storefront_product_search', 40); //prioruty changed to 60
	remove_action('storefront_header', 'storefront_header_container_close', 41); //priority changed to 70
	remove_action('storefront_header', 'storefront_primary_navigation_wrapper', 42); //removed
	remove_action('storefront_header', 'storefront_primary_navigation', 50); //priority changed to 22
	remove_action('storefront_header', 'storefront_header_cart', 60); //removed
	remove_action('storefront_header', 'storefront_primary_navigation_wrapper_close', 68); //removed
	
	add_action('storefront_header', 'npshop_header_company_name', 10); //added
	add_action('storefront_header', 'npshop_header_first_row_wrapper', 8); //added
	add_action('storefront_header', 'storefront_primary_navigation', 22); //priority changed from 50
	add_action('storefront_header', 'npshop_header_cart', 30);//function changed, priority changed from 60
	add_action('storefront_header', 'npshop_header_first_row_wrapper_close', 40); //added
	add_action('storefront_header', 'npshop_header_second_row_wrapper', 41); //added
	add_action('storefront_header', 'npshop_header_site_description', 42); //added
	add_action('storefront_header', 'storefront_site_branding', 50); //priority changed from 20
	add_action('storefront_header', 'storefront_product_search', 60); //priority changed from 40
	add_action('storefront_header', 'storefront_header_container_close', 70);
	add_action('storefront_header', 'npshop_header_second_row_wrapper_close', 71); //added

}

function npshop_header_company_name() {
	echo '<h2>ООО «Алентис Электроникс»</h2>';
}

function npshop_header_first_row_wrapper() {
	echo '<div class="header-first-row-wrapper">';
}

function npshop_header_second_row_wrapper() {
	echo '<div class="header-second-row-wrapper">';
}

function npshop_header_first_row_wrapper_close() {
	echo '</div><!-- .header-first-row-wrapper close -->';
}

function npshop_header_second_row_wrapper_close() {
	echo '</div><!-- .header-second-row-wrapper close -->';
}

function npshop_header_site_description() {
	if ( '' !== get_bloginfo( 'description' ) ) {
		echo '<div class="site-description">' . esc_html( get_bloginfo( 'description', 'display' ) ) . '</div>';
	}
}

//ANCHOR custom header cart link
add_action('init', 'npshop_header_cart_link', 15);
function npshop_header_cart_link() {

	function npshop_header_cart() {
		if ( storefront_is_woocommerce_activated() ) {	
			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
		<ul id="site-header-cart" class="site-header-cart menu">
			<li class="<?php echo esc_attr( $class ); ?>">
				<?php npshop_cart_link(); ?>
			</li>
			<li>
				<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
			</li>
		</ul>
			<?php
		}
	}

	function npshop_cart_link() {
		?>
			<a class="cart-contents npshop-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'storefront' ); ?>">
				<div class="icon_cart_container">
					<!-- <i class="fas fa-shopping-basket"></i> -->
					<div class="npicon icon_cart"></div>
					<span class="circle_count"><?php echo wp_kses_data( sprintf( WC()->cart->get_cart_contents_count() )); ?></span>
				</div>
				<?php //echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?>
			</a>
		<?php
	}

	function npshop_cart_link_fragment( $fragments ) {
		global $woocommerce;

		ob_start();
		npshop_cart_link();
		$fragments['a.cart-contents'] = ob_get_clean();

		ob_start();
		storefront_handheld_footer_bar_cart_link();
		$fragments['a.footer-cart-contents'] = ob_get_clean();

		return $fragments;
	}

	if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
		add_filter( 'woocommerce_add_to_cart_fragments', 'npshop_cart_link_fragment' );
	} else {
		add_filter( 'add_to_cart_fragments', 'npshop_cart_link_fragment' );
	}
}

//ANCHOR header bar
add_action('storefront_before_header', 'npshop_header_bar');
function npshop_header_bar() {
	?>
	<div class="header-bar">
		<div class="col-full">
			<div class="header-bar-container">
				<div class="header-bar-block">Отдел продаж: <a href="mailto:sale@netping.ru">sale@netping.ru</a></div>
				<div class="header-bar-block">Телефон: <a href="tel:+74956468537">+7/495/646-85-37</a></div>
				<div class="header-bar-block">Доставка по России бесплатно</div>
			</div>
		</div>
	</div>
	<?php 
}

//ANCHOR add catalog menu widget under header (for mobile view)
add_action( 'storefront_before_content', 'under_header_widget_on_mobile', 13 );
function under_header_widget_on_mobile() {
	the_widget( 'WC_Widget_Product_Categories', 'title=Каталог&hierarchical=1&max_depth=1', array(
		'before_widget' => '<div class="widget mobile-catalog-widget %s">',
		'before_title'  => '<div class="col-full"><h2 class="widgettitle">',
		'after_title'   => '</div></h2>'
	) );
}

//!SECTION HEADER

//SECTION FOOTER

//ANCHOR customize credit
add_action('init', function() {
	remove_action( 'storefront_footer', 'storefront_credit', 20 );
	add_action( 'storefront_footer', 'npshop_credit', 20 );
	function npshop_credit() {
		?>
		<div class="credit-row">
			<div>ООО «Алентис Электроникс» ©  2005-<?php echo date('Y'); ?></div>
			<a href="#">Политика в отношении обработки и защиты  персональных данных</a>
		</div>
		<?php 
	}
});

//ANCHOR add fixed "Questions" button
add_action( 'storefront_after_footer', 'npshop_questions_button' );
function npshop_questions_button() {
	?>
	<a href="#" class="quest-button">?</a>
	<?php
}

//!SECTION Footer  


//SECTION Widgets

 //ANCHOR include "Recent Posts With Images" widget class
require 'inc/recent_posts_custom_widget.php';

//ANCHOR register "Recent Posts With Images" widget
add_action( 'widgets_init', 'register_rpwi_widget' );
function register_rpwi_widget() {
    register_widget( 'WP_Widget_Recent_Posts_With_Images' );
}


//!SECTION Widgets

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


//SECTION Archive pages





//ANCHOR add short description for product on archive page
add_action('woocommerce_after_shop_loop_item_title', 'add_short_desc_to_archive_product', 3);
function add_short_desc_to_archive_product() {
	global $product;
	// echo '<div class="archive-product-desc">' . substr($product->get_short_description(), 0, 250 ) . '</div>';
	echo '<div class="archive-product-desc">' . wp_trim_excerpt( ) . '</div>';
}

//ANCHOR length of excerpt in words
add_filter( 'excerpt_length', 'change_excerpt_length', 10, 1);
function change_excerpt_length( $length ) {
	return 30;
}

//ANCHOR excerpt more link (default is [...])
add_filter( 'excerpt_more', 'change_excerpt_more_link' );
function change_excerpt_more_link( $more ) {
	return '&hellip;';
}




//ANCHOR change archive page add to cart button behavior
add_filter( 'woocommerce_loop_add_to_cart_link', 'add_to_cart_custom_button' );
function add_to_cart_custom_button( $link ) {
    global $product;
    echo '<a href="'.$product->get_permalink( $product->get_id() ).'" class="button">Подробнее&nbsp;&nbsp;❯</a>';
}

//ANCHOR add label for sorting dropdown 
add_action( 'woocommerce_before_shop_loop', 'add_sort_label', 11 );
function add_sort_label() {
	echo '<span class="sort-label">Сортировать:</span>';
}

//ANCHOR remove result count and change priority for sorting dropdown
add_action( 'wp', 'bbloomer_remove_default_sorting_storefront' );
function bbloomer_remove_default_sorting_storefront() {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
	add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 15 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
}

//ANCHOR remove "by rating" sorting option
add_filter( 'woocommerce_catalog_orderby', 'customize_sorting_options' );
function customize_sorting_options( $options ) {
	unset( $options['rating'] );
	$options['price'] = 'По возрастанию цены';
	$options['price-desc'] = 'По убыванию цены';
	$options['date'] = 'По дате добавления';
	return $options;
}

//ANCHOR output image for category page
add_action( 'woocommerce_before_main_content', 'output_category_image', 30);
function output_category_image() {
	if (is_archive( )) {
		global $wp_query;
		$image = get_field('cat_image', $wp_query->get_queried_object());
		if ($image) {
			?>
			<div class="category-image">
				<img src="<?php echo $image ?>" alt="<?php echo $wp_query->get_queried_object()->name ?>">
			</div>
			<?php
		}
	}
}


//ANCHOR add TinyMCE for category descriptions 
add_action("product_cat_edit_form_fields", 'add_tinymce_for_cat_description_field', 10, 2);
function add_tinymce_for_cat_description_field($term, $taxonomy) {
	$settings = array('wpautop' => true, 
						'media_buttons' => false,
						'quicktags' => false, 
						'textarea_rows' => '30' ); 
    ?>
    <tr valign="top">
        <th scope="row"></th>
        <td>
            <?php wp_editor(html_entity_decode($term->description), 'description', $settings); ?>
			<script type="text/javascript">
				jQuery(function($) {
					$('#wp-description-wrap').remove();
				});
			</script>
        </td>
    </tr>
    <?php
} 

//ANCHOR allow html tags for category description
remove_filter( 'pre_term_description', 'wp_filter_kses' );


//!SECTION Archive pages

//SECTION Single product page

//ANCHOR rearrange single product elements
add_action('npshop_before_gallery', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);

add_action( 'woocommerce_single_product_summary', 'remove_variation_price_range', 1 );
function remove_variation_price_range() {
	global $product;
	if ( $product->is_type( 'variable' ) ) {
    	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
	}

	if ( $product->is_type('simple') ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20);
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10);
	}
}

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);

//ANCHOR Disable sidebar on single product
add_filter( 'is_active_sidebar', 'npshop_remove_sidebar', 10, 2 );
function npshop_remove_sidebar( $is_active_sidebar, $index ) {
    if( $index !== "sidebar-1" ) {
        return $is_active_sidebar;
    }
    if( !is_product() && !is_cart() ) {
        return $is_active_sidebar;
    }
    return false;
}


//SECTION compatible products on single

//ANCHOR add compatible products fields
add_action( 'woocommerce_product_options_related', 'npshop_add_compatible_products_fields' );
function npshop_add_compatible_products_fields() {
	global $post;
	?>
	<p class="form-field">
		<label for="compat_sens_ids">Датчики для контроллеров</label>
		<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="compat_sens_ids" name="compat_sens_ids[]" data-placeholder="Поиск по товарам" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
			<?php
			$product_ids = get_post_meta( $post->ID, '_compatible_sensors_ids', true );

			if ('' != $product_ids ) {
				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
					}
				}
			}
			?>
		</select> <?php echo wc_help_tip( 'Указанные здесь товары будут отображаться под вкладками с описанием в столбце "Датчики для контроллеров"'); // WPCS: XSS ok. ?>
	</p>

	<p class="form-field">
		<label for="compat_access_ids">Аксессуары для контроллеров</label>
		<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="compat_access_ids" name="compat_access_ids[]" data-placeholder="Поиск по товарам" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
			<?php
			$product_ids = get_post_meta( $post->ID, '_compatible_accessories_ids', true );

			if ('' != $product_ids ) {
				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
					}
				}
			}
			?>
		</select> <?php echo wc_help_tip( 'Указанные здесь товары будут отображаться под вкладками с описанием в столбце "Аксессуары для контроллеров"'); // WPCS: XSS ok. ?>
	</p>

	<p class="form-field">
		<label for="compat_devices_ids">Совместимые устройства (для датчиков и аксессуаров)</label>
		<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="compat_devices_ids" name="compat_devices_ids[]" data-placeholder="Поиск по товарам" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
			<?php
			$product_ids = get_post_meta( $post->ID, '_compatible_devices_ids', true );

			if ('' != $product_ids ) {
				foreach ( $product_ids as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) . '</option>';
					}
				}
			}
			?>
		</select> <?php echo wc_help_tip( 'Указанные здесь товары будут проверяться на соместимость в корзине"'); // WPCS: XSS ok. ?>
	</p>
	<?php
}

//ANCHOR save compatible products fields
add_action( 'woocommerce_process_product_meta', 'npshop_save_compatible_products_fields' );
function npshop_save_compatible_products_fields( $post_id ){
    if ( isset($_POST['compat_sens_ids']) ) {
		update_post_meta( $post_id, '_compatible_sensors_ids', $_POST['compat_sens_ids'] );
	}

	if ( isset($_POST['compat_access_ids']) ) {
		update_post_meta( $post_id, '_compatible_accessories_ids', $_POST['compat_access_ids'] );
	}

	if ( isset($_POST['compat_devices_ids']) ) {
		update_post_meta( $post_id, '_compatible_devices_ids', $_POST['compat_devices_ids'] );
	}
	// $compat_sensors = $_POST['compat_sens_ids'];
	// $compat_accessories =  $_POST['compat_access_ids'];
	// $compat_devices =  $_POST['compat_devices_ids'];
    // update_post_meta( $post_id, '_compatible_sensors_ids', $compat_sensors );
	// update_post_meta( $post_id, '_compatible_accessories_ids', $compat_accessories );
	// update_post_meta( $post_id, '_compatible_devices_ids', $compat_devices );
}

//ANCHOR output compatible products
add_action('woocommerce_after_single_product_summary', 'compatible_devices_list', 11 );
function compatible_devices_list() {
	global $product;
	$compat_sens_ids = get_post_meta( $product->get_ID(), '_compatible_sensors_ids', true );
	$compat_access_ids = get_post_meta( $product->get_ID(), '_compatible_accessories_ids', true );

	if ( !empty($compat_sens_ids) || !empty($compat_access_ids)) {
		echo '<h2 class="h-line">Совместимые устройства</h2>';
		echo '<div class="compatible-devices">';
		if ( !empty($compat_sens_ids) ) {
			echo '<div class="compatibles compat-sensors">';
			echo '<h3>Датчики для контроллеров</h3>';
			foreach ( $compat_sens_ids as $compat_sens_id ) {
				$compat_product = wc_get_product($compat_sens_id);
				?>
				<div class="compat-product <?php echo !$compat_product->is_purchasable() ? 'disabled' : '' ?>">
					<?php echo $compat_product->get_image('micro_thumb'); ?>
					<a class="name-link" href="<?php echo $compat_product->get_permalink() ?>">
						<h3><?php echo $compat_product->get_name(); ?> </h3>
					</a>
					<?php woocommerce_quantity_input( ); ?>
					<a href="<?php echo $compat_product->add_to_cart_url() ?>" data-product_id="<?php echo $compat_sens_id ?>" data-quantity="1" class="button npicon icon_cart add_to_cart_button compatible "></a>
				</div>
				<?php
			}
			echo '</div>';
		}

		if ( !empty($compat_access_ids) ) {
			echo '<div class="compatibles compat-accessories">';
			echo '<h3>Аксессуары для контроллеров</h3>';
			foreach ( $compat_access_ids as $compat_access_id ) {
				$compat_product = wc_get_product($compat_access_id);
				?>
				<div class="compat-product <?php echo !$compat_product->is_purchasable() ? 'disabled' : '' ?>">
					<?php echo $compat_product->get_image('micro_thumb'); ?>
					<a class="name-link" href="<?php echo $compat_product->get_permalink() ?>">
						<h3><?php echo $compat_product->get_name(); ?> </h3>
					</a>
					<?php woocommerce_quantity_input( ); ?>
					<a href="<?php echo $compat_product->add_to_cart_url() ?>" data-product_id="<?php echo $compat_access_id ?>" data-quantity="1" class="button npicon icon_cart add_to_cart_button compatible "></a>
				</div>
				<?php
			}
			echo '</div>';
		}

		echo '</div>';
	}

}

//!SECTION compatible products on single


//SECTION radio buttons for variations

//ANCHOR get variation name in loop
function get_mini_name_field($product, $name, $term_slug) {
	foreach ( $product->get_available_variations() as $variation ) {
        if($variation['attributes'][$name] == $term_slug ) {
			return get_post_meta( $variation[ 'variation_id' ], '_mini_name', true );
        }
	}
}

add_filter('woocommerce_dropdown_variation_attribute_options_html', 'variation_radio_buttons', 20, 2);
function variation_radio_buttons($html, $args) {
	$args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
	  'options'          => false,
	  'attribute'        => false,
	  'product'          => false,
	  'selected'         => false,
	  'name'             => '',
	  'id'               => '',
	  'class'            => '',
	  'show_option_none' => __('Choose an option', 'woocommerce'),
	));
  
	if(false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product) {
		$selected_key     = 'attribute_'.sanitize_title($args['attribute']);
		$args['selected'] = isset($_REQUEST[$selected_key]) ? wc_clean(wp_unslash($_REQUEST[$selected_key])) : $args['product']->get_variation_default_attribute($args['attribute']);
	}
  
	$options               = $args['options'];
	$product               = $args['product'];
	$attribute             = $args['attribute'];
	$name                  = $args['name'] ? $args['name'] : 'attribute_'.sanitize_title($attribute);
	$id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
	$class                 = $args['class'];
	$show_option_none      = (bool)$args['show_option_none'];
	$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce');
  
	if(empty($options) && !empty($product) && !empty($attribute)) {
		$attributes = $product->get_variation_attributes();
		$options    = $attributes[$attribute];
	}
	

	$radios = '<div class="variation-radios">';

	if(!empty($options)) {
		if($product && taxonomy_exists($attribute)) {
			$terms = wc_get_product_terms($product->get_id(), $attribute, array(
				'fields' => 'all',
			));
	
			foreach($terms as $term) {
				if(in_array($term->slug, $options, true)) {
					$id = $name.'-'.$term->slug;
					$mini_name = get_mini_name_field($product, $name, $term->slug);
					$radios .= '<div class="radio_wrapper"><input class="like-check" type="radio" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="'.esc_attr($term->slug).'" '.checked(sanitize_title($args['selected']), $term->slug, false).'><label for="'.esc_attr($id).'">'.esc_html( $mini_name ? $mini_name : apply_filters('woocommerce_variation_option_name', $term->name) ).'</label></div>';
				}
			}
		} else {
			foreach($options as $option) {
				$id = $name.'-'.$option;
				$mini_name  = get_mini_name_field($product, $name, $option);
				$checked    = sanitize_title($args['selected']) === $args['selected'] ? checked($args['selected'], sanitize_title($option), false) : checked($args['selected'], $option, false);
				$radios    .= '<div class="radio_wrapper"><input  class="like-check" type="radio" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="'.esc_attr($option).'" id="'.sanitize_title($option).'" '.$checked.'><label for="'.esc_attr($id).'">'.esc_html($mini_name ? $mini_name : apply_filters('woocommerce_variation_option_name', $option)).'</label></div>';
			}
		}
	}
  
	$radios .= '</div>';

	return $html.$radios;
}

//ANCHOR check if variation is active
add_filter('woocommerce_variation_is_active', 'variation_check', 10, 2);
function variation_check($active, $variation) {
	if(!$variation->is_in_stock() && !$variation->backorders_allowed()) {
		return false;
	}
	return $active;
}

//ANCHOR load variations settings
add_filter( 'woocommerce_available_variation', 'load_variation_settings_fields' );
function load_variation_settings_fields( $variations ) {
	$is_eol = get_post_meta( $variations[ 'variation_id' ], '_is_eol', true );

	if ($is_eol) {
		$variations['eol'] = $is_eol;
		$variations['eol_html'] = get_post_meta( $variations[ 'variation_id' ], '_eol_html', true );
	}

	$product = wc_get_product( $variations[ 'variation_id' ]);
	$parent_pr = wc_get_product($product->get_parent_id());
	
	$variations['product_title'] = $product->get_name();
	
	return $variations;

}

//ANCHOR trim Parent product name from variation title
add_filter( 'woocommerce_product_variation_title', 'trim_variation_title');
function trim_variation_title($title_base) {
	// return stristr( $title_base, '-' );
	$title_base = explode('-', $title_base);
	$title_base = array_pop( $title_base );
	return $title_base;
}


//!SECTION radio buttons for variations

//ANCHOR meta fields for variations

//ANCHOR inputs for variations meta fields
add_action( 'woocommerce_product_after_variable_attributes', 'npshop_variation_settings_fields', 10, 3 );
function npshop_variation_settings_fields( $loop, $variation_data, $variation ) {
	woocommerce_wp_text_input( 
		array( 
			'id'          => '_is_eol[' . $variation->ID . ']', 
			'label'       => 'EOL', 
			'placeholder' => 'Укажите рекомендуемую модификацию',
			'desc_tip'    => 'true',
			'description' => 'Если модель снимается с производства - укажите рекомендуемую замену, иначе оставьте поле пустым',
			'value'       => get_post_meta( $variation->ID, '_is_eol', true )
		)
	);

	woocommerce_wp_text_input( 
		array( 
			'id'          => '_mini_name[' . $variation->ID . ']', 
			'label'       => 'Сокращенное имя', 
			'placeholder' => 'Сокращенное имя, отображается рядом с чекбоксом',
			'desc_tip'    => 'true',
			'description' => 'Укажите короткое название (для чекбокса), если оставить поле пустым, будет отображаться полное название вариации',
			'value'       => get_post_meta( $variation->ID, '_mini_name', true )
		)
	);
}

//ANCHOR save variations meta fileds
add_action( 'woocommerce_save_product_variation', 'npshop_save_variation_settings_fields', 10, 2 );
function npshop_save_variation_settings_fields( $post_id ) {
	$is_eol = $_POST['_is_eol'][ $post_id ];
	$mini_name = $_POST['_mini_name'][ $post_id ];
	if( isset( $is_eol ) ) {
		$html .= '<div class="var-opt-label"><span>End of Life</span></div><div class="var-opt-text"><span>Снимается с производства</span><span>Рекомендуем: <strong> Модификация ' . $is_eol . '</strong></span></div>';
		update_post_meta( $post_id, '_eol_html', $html );
		update_post_meta( $post_id, '_is_eol', esc_attr( $is_eol ) );
	}

	if ( isset( $mini_name )) {
		update_post_meta( $post_id, '_mini_name', esc_attr( $mini_name ) );
	}
}




//ANCHOR wrap not sale prices of variations
add_filter( 'woocommerce_available_variation', 'my_variation', 10, 3);
function my_variation( $data, $product, $variation ) {
    if (!$variation->is_on_sale()) {
		$data['price_html'] = '<div class="not-sale-var-price">' . $data['price_html'] . '</div>';
	}
	return $data;
}

//ANCHOR customize single page product tabs
add_filter( 'woocommerce_product_tabs', 'npshop_custom_tab' );
function npshop_custom_tab( $tabs ) {
	unset($tabs['additional_information']);
	unset( $tabs['reviews'] );
	$tabs['delivery'] = array(
		'title'     => 'Покупка и доставка',
		'priority'  => 70,
		'callback'  => 'npshop_delivery_tab_content'
	);
	$tabs['warranty'] = array(
		'title'    =>'Возврат и гарантия',
		'priority' => 80,
		'callback' => 'npshop_warranty_tab_content'
	);
    return $tabs;
}

function npshop_delivery_tab_content() {
	echo get_page_by_path('delivery')->post_content;
}

function npshop_warranty_tab_content() {
	echo get_page_by_path('warranty')->post_content;
}

//ANCHOR remove heading from description product tab
add_filter( 'woocommerce_product_description_heading', '__return_false' );


//ANCHOR add navigation arrows for single product gallery
add_filter('woocommerce_single_product_carousel_options', 'update_woo_flexslider_options');
function update_woo_flexslider_options($options) {
      $options['directionNav'] = true;
      return $options;
  }


//ANCHOR change columns for thumbnails in product gallery
add_filter( 'woocommerce_single_product_image_gallery_classes', 'npshop_product_gallery_thumbnails_columns' );
function npshop_product_gallery_thumbnails_columns( $wrapper_classes ) {
   $columns = 3;
   $wrapper_classes[2] = 'woocommerce-product-gallery--columns-' . absint( $columns );
   return $wrapper_classes;
}

//ANCHOR change upsells columns on single product
add_filter( 'woocommerce_upsell_display_args', 'change_upsells_columns', 20 );
function change_upsells_columns( $args ) {
	$args['columns'] = 4;
	return $args;
}

//ANCHOR add Full description link on single product
add_action('woocommerce_after_single_product_summary', 'catalog_link_after_tabs');
function catalog_link_after_tabs() {
	?>
	<div class="catalog-link">
		<span class="info-icon"></span>
		<span class="info-text">Подробное описание устройства мы можете изучить в каталоге на сайте производителя</span>
		<a href="#" class="button">Перейти в каталог</a>
	</div>
	<?php
}

//!SECTION Single product

//SECTION Checkout process customizations

//ANCHOR redirect cart page to checkout
add_action('template_redirect', 'npshop_skip_cart_page_redirection_to_checkout');
function npshop_skip_cart_page_redirection_to_checkout() {
	global $wp;
    if ( 'cart' == $wp->request && WC()->cart->cart_contents_count !== 0 ) {
        wp_redirect( wc_get_checkout_url() );
		exit();
	}
}

//ANCHOR remove added to cart meassge
add_filter( 'wc_add_to_cart_message_html', 'npshop_remove_add_to_cart_message' );
function npshop_remove_add_to_cart_message( $message ){
	return '';
}

//ANCHOR display sale prices in cart table
add_filter( 'woocommerce_cart_item_price', 'npshop_add_cart_item_sale_price', 30, 3 );
function npshop_add_cart_item_sale_price( $price, $values, $cart_item_key ) {
	$sale_price = $values['data']->get_price_html();
	$is_on_sale = $values['data']->is_on_sale();
	if ( $is_on_sale ) {
		$price = $sale_price;
	}
	return $price;
}

//ANCHOR custom order review on checkout page
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
add_action( 'woocommerce_checkout_order_review', 'npshop_order_review', 10 );
function npshop_order_review() {
	?>
	<div class="npshop-order-review">
		<p>Общая сумма заказа:</p>
		<p id="cart-total-price" class="order-total"><?php wc_cart_totals_order_total_html() ?></p>
		<p>НДС включен в цену товара</p>
		<p>Доставка по России БЕСПЛАТНО</p>
		<p style="font-size:14px; margin-bottom:0">Подробнее о покупке и достaвке <a href="">здесь</a></p>
	</div>
	<?php
}

//ANCHOR change "Place order" button text on checkout
add_filter('woocommerce_order_button_text', function() {return 'Оформить заказ';} );

//ANCHOR featured products section on checkout and Empty cart pages
add_action( 'woocommerce_after_checkout_form', 'add_featured_products_section' );
function add_featured_products_section() {
	echo ('<h2 class="h-line">Рекомендуем</h2>');
	echo do_shortcode('[products limit="4" columns="4" visibility="featured" ]');
	// global $woocommerce_product_loop;
	// var_dump($woocommerce_product_loop);
}


//SECTION share cart link

//ANCHOR add multiple products to cart by url
// Fire before the WC_Form_Handler::add_to_cart_action callback.
add_action( 'wp_loaded', 'add_multiple_products_to_cart', 15 );
function add_multiple_products_to_cart( $url = false ) {
	// Make sure WC is installed, and add-to-cart query arg exists, and contains at least one comma.
	if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
		return;
	}

	//empty cart if url contains comma
	if ( ! false == strpos( $_REQUEST['add-to-cart'], ',' ) ) {
		if( ! WC()->cart->is_empty() ) {
			WC()->cart->empty_cart();
		}
	}

	//Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
	remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

	$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
	$count       = count( $product_ids );
	$number      = 0;

	foreach ( $product_ids as $id_and_quantity ) {
		// Check for quantities defined in curie notation (<product_id>:<product_quantity>)
		$id_and_quantity = explode( ':', $id_and_quantity );
		$product_id = $id_and_quantity[0];

		$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

		if ( ++$number === $count ) {
			// Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
			$_REQUEST['add-to-cart'] = $product_id;

			return WC_Form_Handler::add_to_cart_action( $url );
		}

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$was_added_to_cart = false;
		$adding_to_cart    = wc_get_product( $product_id );

		if ( ! $adding_to_cart ) {
			continue;
		}

		$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

		// Variable product handling
		if ( 'variable' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

		// Grouped Products
		} elseif ( 'grouped' === $add_to_cart_handler ) {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

		// Custom Handler
		} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
			do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

		// Simple Products
		} else {
			woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
		}
	}
}

/**
 * Invoke class private method
 *
 * @since   0.1.0
 *
 * @param   string $class_name
 * @param   string $methodName
 *
 * @return  mixed
 */
function woo_hack_invoke_private_method( $class_name, $methodName ) {
	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
	}

	$args = func_get_args();
	unset( $args[0], $args[1] );
	$reflection = new ReflectionClass( $class_name );
	$method = $reflection->getMethod( $methodName );
	$method->setAccessible( true );

	//$args = array_merge( array( $class_name ), $args );
	$args = array_merge( array( $reflection ), $args );
	return call_user_func_array( array( $method, 'invoke' ), $args );
}

//ANCHOR make link for cart share
function cart_share_link_html( ) {
	$html = '<a href="/cart/?add-to-cart=';

	foreach( WC()->cart->get_cart() as $cart_item ) {
		if ( $cart_item['data']->post_type == 'product_variation' ) {
			$html .= $cart_item['variation_id'];
			$html .= ',';
		} else {
			$html .= $cart_item['product_id'];
			$html .= ',';
		}
	}

	$html .= '">Поделиться корзиной</a>';
	return $html;
}


function cart_ya_share_link_html( ) {
	$share_link = home_url() .'/cart/?add-to-cart=';

	foreach( WC()->cart->get_cart() as $cart_item ) {
		if ( $cart_item['data']->post_type == 'product_variation' ) {
			$share_link .= $cart_item['variation_id'];
			$share_link .= ':' . $cart_item['quantity'];
			$share_link .= ',';
		} else {
			$share_link .= $cart_item['product_id'];
			$share_link .= ':' . $cart_item['quantity'];
			$share_link .= ',';
		}
	}

	$ya_share_link = '<script src="https://yastatic.net/share2/share.js"></script><div class="ya-share2" data-curtain data-shape="round" data-limit="0" data-more-button-type="long" data-copy="first" data-services="vkontakte,telegram,facebook,twitter" data-url="' . $share_link . '"></div>';
	return $ya_share_link;
}



// <div class="ya-share2" data-curtain data-shape="round" data-limit="0" data-more-button-type="short" data-services="vkontakte,facebook,telegram,twitter"></div>

//ANCHOR AJAX Event to update share cart link (ajax call is in checkoutscripts.js)
add_action('wp_ajax_update_share_cart_link', 'update_share_cart_link');
add_action('wp_ajax_nopriv_update_share_cart_link', 'update_share_cart_link');
function update_share_cart_link() {
    global $woocommerce;
    $send_json = array();
    $send_json = array('success'=>false);
    if( $woocommerce->cart->get_cart_total() ) {
        $send_json = array('success'=>true, 'shareCartlink'=> cart_ya_share_link_html() );
		// $send_json = array('success'=>true, 'shareCartlink'=> 'поделиться корзиной' );
    };
    wp_send_json($send_json);
    die();
}

//!SECTION share cart link

//ANCHOR Empty cart page customizations
remove_action( 'wc_empty_cart_message', 'wc_empty_cart_message', 10 );
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
add_filter('woocommerce_return_to_shop_text', function() { return 'Начать покупки'; } );
add_filter('woocommerce_return_to_shop_redirect', function() {return '/'; } );

//ANCHOR AJAX Event to update custom cart total price element (ajax call is in checkoutscripts.js)
add_action('wp_ajax_update_cart_total_price', 'update_cart_total_price');
add_action('wp_ajax_nopriv_update_cart_total_price', 'update_cart_total_price');
function update_cart_total_price() {
    global $woocommerce;
    $send_json = array();
    $send_json = array('success'=>false);
    if( $woocommerce->cart->get_cart_total() ) {
        $send_json = array('success'=>true, 'cartTotal'=>$woocommerce->cart->get_cart_total());
    };
    wp_send_json($send_json);
    die();
}


//!SECTION Checkout process custiomizations


