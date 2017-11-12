/**
 * Created by Vitaly on 29.11.2016.
 */

jQuery(function ($) {

    function showProgress() {
        $.each($('.fade-cover'), function (index, value) {
            if ($(value).attr('id') !== 'review_animate_loader') {
                $(value).remove();
            }
        });
        ADS.coverShow();
    }

    var ProductPost = {

        reallySave : function() {

            var data = {
                ID          : $('#post_ID').val(),
                productUrl  : $('#_productUrl').val(),
                storeUrl    : $('#_storeUrl').val(),
                storeName   : $('#_storeName').val(),
                needUpdate  : $('#_needUpdate').is(':checked') ? 1 : 0
            };

            $.ajaxQueue( {
                url     : ajaxurl,
                data    : {
                    action : 'adsw_save_adswsupplier',
                    data   : data
                },
                type    : "POST",
                success : function ( response ) {
                    ADS.notify(response);
                    ADS.coverHide();
                }
            } );
        },

        saveSupplier : function( e ){

            var $obj       = e.obj,
                productUrl = e.url;

            var product = window.ADS.aliParseProduct.parseObj($obj, productUrl);
            console.log(product);
            if( product.shopName.length ) {
                $('#_product_id').val(product.id);
                $('#_storeUrl').val(product.shopLink);
                $('#_storeName').val(product.shopName);

                this.reallySave();
            } else {
                ADS.notify($('#update-fail-message').val());
                ADS.coverHide();
            }
        },

        saveSupplierInfo : function(){
            showProgress();

            var url = $('#_productUrl').val();

            if( window.ADS.isURL(url) )
                window.ADS.aliExpansion.addTask( url, ProductPost.saveSupplier, this, $('#post_ID').val());
            else
                this.reallySave();
        },

        saveProduct : function( e ){

            var $obj       = e.obj,
                productUrl = e.url,
                post_id    = e.index;

            var product = window.ADS.aliParseProduct.parseObj($obj, productUrl);

            $.ajaxQueue( {
                url      : ajaxurl,
                dataType : 'json',
                data     : {
                    action  : 'adsw_reset_product_ali',
                    post_id : post_id,
                    product : ADS.b64EncodeUnicode( JSON.stringify( product ) )
                },
                type     : "POST",
                success  : function ( response ) {

                    if( response.hasOwnProperty('error') ) {
                        ADS.notify( response );
                        ADS.coverHide();
                    } else {
                        ProductPost.images(response);
                    }
                }
            } );
        },

        images : function( product ) {

            $.ajaxQueue( {
                url      : ajaxurl,
                dataType : 'json',
                data     : {
                    action  : 'adsw_uploadExternalImages',
                    ads_actions : 'upload_images_list',
                    product : product
                },
                type     : "POST",
                success  : function ( response ) {

                    if( response.hasOwnProperty('success') ) {
                        ADS.coverHide();
                        location.reload();
                    } else {
                        ProductPost.images( response.product );
                    }
                }
            } );
        },

        resetProduct : function(){
            showProgress();

            var url = $('#_productUrl').val();

            if( window.ADS.isURL(url) )
                window.ADS.aliExpansion.addTask( url, ProductPost.saveProduct, this, $('#post_ID').val());
            else{
                ADS.notify($('#update-fail-message').val());
                ADS.coverHide();
            }
        },

        eachSelecter : function( $el ) {

            $el.each(function(){

                if( ! $(this).hasClass('ads-set') ) {

                    $(this).addClass('ads-set');

                    var txt = $(this).text().replace('×', '');

                    $(this).html(
                        '<a href="#" onclick="adswDelete();return false;" class="select2-search-choice-remove">' +
                        '<i class="dashicons dashicons-no-alt"></i></a><span>' + txt +'</span>'+
                        '<a href="#" onclick="adswShowModal();return false;" class="select2-search-choice-edit" ' +
                        'data-target="#adsw-editAttribute"><i class="dashicons dashicons-edit"></i></a>' );
                }
            })
        },

        applySelecter : function() {

            var $obj = this;

            $('.woocommerce_attribute').on('click', 'h3', function() {
                var $th = $(this).parent();
                $th.find('.select2-selection').off('click');
                $th.find('.select2-selection__choice__remove').on('click');
                if( ! $th.hasClass('ads-attr') ) {

                    $th.addClass('ads-attr');

                    var v = $th.find('[name^="attribute_variation"]').eq(0);

                    if( v.prop('checked') ) {
                        $obj.eachSelecter( $th.find('.select2-selection__choice') );
                    }
                }
            });
        },

        init : function(){

            var $this = this;

            $('.save_adswsupplier').on('click', function(){
                $this.saveSupplierInfo();
            });

            $('.reset_adswvariations').on('click', function(){
                $this.resetProduct();
            });

            $this.applySelecter();
        }
    };

    ProductPost.init();

    var updateProduct = (function () {

        var $this, $body, post_id, productUrl;

        function sendUpdateProduct(e) {

            var $obj = e.obj,
                post_id = e.index,
                product = window.ADS.aliParseProduct.parseObj($obj, productUrl);

            data = {
                product: ADS.b64EncodeUnicode(JSON.stringify(product)),
                setting: {
                    status : $('#js-post_status').val(),
                    cost   : $('#js-post_cost').val(),
                    stock  : $('#js-post_stock').val()
                }
            };

            data.action = 'adsw_update_product';
            data.ads_actions = 'update';
            data.post_id = post_id;
            $.ajaxQueue({
                url: ajaxurl,
                data: data,
                type: "POST",
                success: function (response) {
                    response = ADS.tryJSON(response);
                    location.reload();
                }
            });
        }

        return {
            init: function () {
                $this = this;
                $body = $('body');
                post_id = $('#post_ID').val();
                $body.on('click', '#js-post_updateProduct', $this.update);

            },
            update: function (e) {
                e.preventDefault();
                productUrl = $('#_productUrl').val();
                showProgress();
                window.ADS.aliExpansion.addTask(productUrl, sendUpdateProduct, $this, post_id);
            }
        }
    })();

    updateProduct.init();
});