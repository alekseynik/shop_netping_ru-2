(function($) {
    $(document).ready(function(){
        $('.cat_toggle').click(function() {
            $('.cat_toggle').toggleClass('toggled');
        });

        $('.mobile-catalog-widget .widgettitle').click(function() {
            $('.mobile-catalog-widget').toggleClass('toggled');
        });



    });
})(jQuery);
