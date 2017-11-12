/**
 * Created by Vitaly on 08.12.2016.
 */

adswShow = null;
adswRemove = null;

jQuery(function($) {

    function aShow(e){
        var $p = $( e.target ).parent(),
            $attr = $('#adsw-attribute'),
            name = $p.parent().text().replace('×', '');

        var mod = $p.data('target'),
            $td = $p.parents('td');

        var t = $('.adsw-this-end');

        if( t.length )
            t.removeClass('adsw-this-end');

        $attr.html('');

        $td.addClass('adsw-this-end');

        $('#adsw-title').text(name);

        var selected = false;

        $td.find('select option').each(function(){

            $attr.append('<option value="' + $(this).attr('value') + '">' + $(this).text() + '</option>');

            if ($(this).text() === name){
                selected = $(this).attr('value');
            }
        });

        if( selected )
            $attr.val(selected);

        $(mod).modal('show').on('shown.bs.modal', function(){
            $attr.selectpicker('refresh');
        });
    }
    adswShow = aShow;

    function aDelete(e){
        var $p = $( e.target ).parents('li'),
            name = $p.text().replace('×', ''),
            $td = $p.parents('td');

        $td.find('select option').each(function(){
            if( $(this).text() === name ) {
                $(this).prop("selected", false);
            }
        });

        $p.remove();
    }
    adswRemove = aDelete;

    $('#adsw-apply').on('click', function(){

        var old   = $('#adsw-title').text(), //старое название аттрибутов
            $attr = $('.adsw-this-end'), //текущая select2
            $new  = $('#adsw-attribute'),
            taxonomy = $attr.parents('.woocommerce_attribute').data('taxonomy'); //таксономия

        $attr.find('ul li span').each(function(){

            if( $(this).text() === old )
                $(this).text( $new.find(":selected").text() );
        });

        $attr.find('select.attribute_values option[value="'+$new.val()+'"]').attr('selected', true);

        $.ajaxQueue( {
            url     : ajaxurl,
            data    : {
                action : 'adsw_save_adswattrib',
                data   : {
                    old      : old,
                    taxonomy : taxonomy,
                    term     : $new.val(),
                    post_ID  : $('#post_ID').val()
                }
            },
            type    : "POST",
            success : function ( response ) {

                var data = ADS.tryJSON(response);

                if( typeof data.success === 'undefined' ) {
                    ADS.notify(data.error);
                }
                else{
                    ADS.notify(data.success);
                }

                $( '#variable_product_options' ).trigger( 'reload' );
            }
        } );
    });
});

function adswShowModal () {
    adswShow(event);
}

function adswDelete() {
    adswRemove(event);
}
