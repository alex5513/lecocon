/**
 * Created by Vitaly on 18.02.2017.
 */
jQuery(function($){

    baguetteBox.run('.adsw-gallery');

    $('.sku-set').on('click', function(){

        if( ! $(this).hasClass('active') && ! $(this).hasClass('sku-disabled') ) {
            var t = $(this),
                d = t.parent();

            d.addClass('clicked').find('.active').removeClass('active');
            t.addClass('active');

            var p = t.parents('td'),
                s = p.find('select');

            s.val(t.data('value')).change();

            if( t.hasClass('meta-item-img') ) {
                var image_id = t.data('image_id'),
                    $form = t.parents('form'),
                    variation = false;

                $.each( $form.data( 'product_variations' ),function(i,v){
                    if( image_id === parseInt(v.image_id) ) {
                        variation = v;
                    }
                });

                if( variation )
                    $form.wc_variations_image_update( variation );
            }
        }
    });

    $( 'form.variations_form' ).on('check_variations', function(){

        var th = $(this);

        setTimeout(function(){
            th.find('.adsw-attribute-option').each(function(){

                if( ! $(this).hasClass('clicked') ) {
                    var v = [];

                    $(this).parent().find('select option').each(function(){
                        if( this.value !== '') v.push(this.value.toString());
                    });

                    $(this).find('.sku-set').each(function(){

                        var vv = $(this).data('value').toString();

                        if( $.inArray(vv, v) !== -1 ) {
                            $(this).removeClass('sku-disabled');
                        } else {
                            $(this).addClass('sku-disabled').removeClass('active');
                        }
                    });
                } else {
                    $(this).removeClass('clicked')
                }
            });
        }, 200);

    });

    $( document ).on( 'click', 'a.reset_variations', function() {
        $(this).parents('form').find('.sku-set').removeClass('active').removeClass('sku-disabled');
    } );
});