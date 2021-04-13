<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php do_action( 'storefront_before_footer' ); ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="col-full">

			<?php
			/**
			 * Functions hooked in to storefront_footer action
			 *
			 * @hooked storefront_footer_widgets - 10
			 * @hooked storefront_credit         - 20
			 */
			do_action( 'storefront_footer' );
			?>

		</div><!-- .col-full -->
	</footer><!-- #colophon -->

	<?php do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

<script>
	'use strict';

function Tabs() {
  var bindAll = function() {
    var menuElements = document.querySelectorAll('[data-tab]');
    for(var i = 0; i < menuElements.length ; i++) {
      menuElements[i].addEventListener('click', change, false);
    }
  }

  var clear = function() {
    var menuElements = document.querySelectorAll('[data-tab]');
    for(var i = 0; i < menuElements.length ; i++) {
      menuElements[i].classList.remove('active');
      var id = menuElements[i].getAttribute('data-tab');
      document.getElementById(id).classList.remove('active');
    }
  }

  var change = function(e) {
	  e.preventDefault();
    clear();
    // console.log(e.target);
    e.currentTarget.classList.add('active');
    var id = e.currentTarget.getAttribute('data-tab');
    console.log(id);
    document.getElementById(id).classList.add('active');
  }

  bindAll();
}

var connectTabs = new Tabs();

</script>

<script>
jQuery(document).on('change', '.variation-radios input', function() {
	jQuery('.variation-radios input:checked').each(function(index, element) {
		var $el = jQuery(element);
		var thisName = $el.attr('name');
		var thisVal  = $el.attr('value');
		jQuery('select[name="'+thisName+'"]').val(thisVal).trigger('change');
  });
});
jQuery(document).on('woocommerce_update_variation_values', function() {
	jQuery('.variation-radios input').each(function(index, element) {
		var $el = jQuery(element);
		var thisName = $el.attr('name');
		var thisVal  = $el.attr('value');
		$el.removeAttr('disabled');
		if(jQuery('select[name="'+thisName+'"] option[value="'+thisVal+'"]').is(':disabled')) {
			$el.prop('disabled', true);
		}
  });
});

//qty input handler
jQuery( "body" ).on( "click", ".quantity input", function() {
    return false;
});
jQuery( "body" ).on( "change input", ".quantity .qty", function() {
    var add_to_cart_button = jQuery( this ).parents( ".compat-product" ).find( ".add_to_cart_button" );
    // For AJAX add-to-cart actions
    add_to_cart_button.attr( "data-quantity", jQuery( this ).val() );
    // For non-AJAX add-to-cart actions
    add_to_cart_button.attr( "href", "?add-to-cart=" + add_to_cart_button.attr( "data-product_id" ) + "&quantity=" + jQuery( this ).val() );
});
jQuery("body").on("click", ".add_to_cart_button", function() {
    jQuery(".quantity .qty").val(1);
});	

//+ and - qty buttons handler (https://stackoverflow.com/questions/52367826/custom-plus-and-minus-quantity-buttons-in-woocommerce-3)
jQuery( function( $ ) {
    if ( ! String.prototype.getDecimals ) {
        String.prototype.getDecimals = function() {
            var num = this,
                match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
            if ( ! match ) {
                return 0;
            }
            return Math.max( 0, ( match[1] ? match[1].length : 0 ) - ( match[2] ? +match[2] : 0 ) );
        }
    }
    // Quantity "plus" and "minus" buttons
    $( document.body ).on( 'click', '.plus, .minus', function() {
        var $qty        = $( this ).closest( '.quantity' ).find( '.qty'),
            currentVal  = parseFloat( $qty.val() ),
            max         = parseFloat( $qty.attr( 'max' ) ),
            min         = parseFloat( $qty.attr( 'min' ) ),
            step        = $qty.attr( 'step' );

        // Format values
        if ( ! currentVal || currentVal === '' || currentVal === 'NaN' ) currentVal = 0;
        if ( max === '' || max === 'NaN' ) max = '';
        if ( min === '' || min === 'NaN' ) min = 0;
        if ( step === 'any' || step === '' || step === undefined || parseFloat( step ) === 'NaN' ) step = 1;

        // Change the value
        if ( $( this ).is( '.plus' ) ) {
            if ( max && ( currentVal >= max ) ) {
                $qty.val( max );
            } else {
                $qty.val( ( currentVal + parseFloat( step )).toFixed( step.getDecimals() ) );
            }
        } else {
            if ( min && ( currentVal <= min ) ) {
                $qty.val( min );
            } else if ( currentVal > 0 ) {
                $qty.val( ( currentVal - parseFloat( step )).toFixed( step.getDecimals() ) );
            }
        }

        // Trigger change event
        $qty.trigger( 'change' );
    });
});
</script>

<script>
  jQuery(function($){
	// add a new toggle element after the parent category anchor
	$( "<div class='woo-cat-toggle'>></div>" ).insertAfter( "#secondary .widget_product_categories .product-categories > .cat-item.cat-parent > a" );

  $("#secondary .widget_product_categories .product-categories > .cat-item.cat-parent > a, #secondary .widget_product_categories .product-categories > .cat-item.cat-parent > .woo-cat-toggle").wrapAll("<div class='flex-wrapper'></div>");

	// when the new toggle element is clicked, add/remove the class that controls visibility of children
	$( "#secondary .widget_product_categories .product-categories .woo-cat-toggle" ).click(function () {
		$(this).toggleClass("cat-popped");
    $(this).parent().toggleClass("popped-wrapper");
	});

	// if the parent category is the current category or a parent of an active child, then show the children categories too
	$('#secondary .widget_product_categories .product-categories > .cat-item.cat-parent').each(function(){
		if($(this).is('.current-cat, .current-cat-parent')) {
			$(this).children('.woo-cat-toggle').toggleClass("cat-popped");
		} 
	});
});
</script>

</body>
</html>
