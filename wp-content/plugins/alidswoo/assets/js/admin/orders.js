/**
 * Created by Vitaly on 29.12.2016.
 */

jQuery( function($) {

    var autofilling = {
        tip : function() {

            var $modal = $('#panel-modal');

            $('.js-change-tip').on('click', function(){

                var $th    = $(this).parents('.item-inline'),
                    tip    = $th.find('.adsw-tip').val(),
                    order  = $th.find('.adsw-order_number').val(),
                    id     = $th.data('item_id');

                $modal.find('#pid').val( $.trim(id) );
                $modal.find('#tip').val( $.trim(tip) );
                $modal.find('#order_number').val( $.trim(order) );

                $modal.modal('show');
            });

            $('#saveTIP').on('click', function(){

                var $id    = $modal.find('#pid'),
                    $tip   = $modal.find('#tip'),
                    $order = $modal.find('#order_number'),
                    i      = $.trim( $id.val() ),
                    t      = $.trim( $tip.val() ),
                    o      = $.trim( $order.val() );

                var co     = '.adsw_order_number',
                    ct     = '.adsw_tracking_id',
                    gt     = '.get-tracking';

                $.ajaxQueue({
                    url: ajaxurl,
                    data: {action: 'adsw_save_tip', id: i, tip: t, order: o},
                    type: "POST",
                    success: function (response){

                        if( response === 'done' ){

                            var $th = $('#order_line_items').find('[data-item_id="'+i+'"]'),
                                $tr = $th.parents('tr');

                            var fco = $tr.find(co);

                            $th.find('.adsw-order_number').val(o);
                            $th.find('.adsw-tip').val(t);

                            if(o){
                                fco.html('<a href="http://trade.aliexpress.com/order_detail.htm?orderId=' + o + '" target="_blank">' + o + '</a>');
                                $tr.find(gt).show();
                            } else {
                                fco.html(fco.data('default'));
                                $tr.find(gt).hide();
                            }

                            if( t ) {
                                $tr.find(ct).html('<a href="http://www.17track.net/?nums=' + t + '" target="_blank">' + t + '</a>');
                                $tr.find(gt).hide();
                            } else {
                                $tr.find(ct).text(
                                    $tr.find(ct).data('default')
                                );
                            }
                        }
                    }
                });

                $modal.modal('hide');

                $modal.find('#pid').val('');
                $modal.find('#tip').val('');
                $modal.find('#order_number').val('');
            });

            /**
             * получение tracking для одного продукта из заказа
             */
            $('.item-inline').on('click', '.js-get-tracking', function (  ) {

                var $row = $(this).parents('.item-inline');

                var item_id = $row.data('item_id');
                var order_number = $row.find('.adsw-order_number').val();

                if(order_number && item_id){
                    window.ADS.aliExpansion.getTIPOrder(item_id, order_number);
                }
            });

            /**
             * получение tracking для всех продуктов из заказа
             */
            $('#get-tracking-line').on( 'click', '.button', function () {

                var panelOrders = $( '#order_line_items' );

                var ordersIdAli = [];

                panelOrders.find( '.item-inline' ).each( function ( i, e ) {
                    var order_number = $(e).find( '.adsw-order_number' ).val();
                    var tip = $(e).find( '.adsw-tip' ).val();

                    if ( !tip && order_number ) {
                        ordersIdAli.push( order_number )
                    }
                } );

                if ( ordersIdAli.length ) {
                    window.ADS.aliExpansion.getTIPOrders( ordersIdAli );
                }

            } );

            $('.item-inline').on( 'click', '.js-placeorder-manually', function () {

                var product_url = $(this).attr('href');
                var $row = $(this).closest('.item-inline');

                var orderIdStore = $row.data('item_id');
                var order_number = $row.find('.adsw-order_number').val();
                var product_id = $row.data('product_id');

                if(window.AvailableExtensions.is() && !order_number && orderIdStore && product_id){
                    window.ADS.aliExpansion.placeOrderManually( product_url, product_id, orderIdStore );
                    return false;
                }

            } );

            window.ADS.Dispatcher.on('adsGoogleExtension:setOrderTIP', function ( e ) {

                if(typeof e.orderIdStore === 'undefined' || typeof e.orderDetail.trackingNo === 'undefined'){
                    var error = $('#msg-tip-error').text();
                    window.ADS.notify( error );
                    return;
                }

                var trackingNo = e.orderDetail.trackingNo.trim();

                var $row = $('[data-item_id="'+e.orderIdStore+'"]');

                var success = $('#msg-tip-success').text();

                success = success.replace('{trackingNo}', trackingNo);

                if($row.length){
                    $row.data('tip', trackingNo);
                    $row.parents('tr').find('.adsw_tracking_id').hide(0)
                        .html('<a href="http://www.17track.net/?nums=' + trackingNo + '" target="_blank">' + trackingNo + '</a>')
                        .show(300);

                    window.ADS.notify( success );
                }

            }, this);


            window.ADS.Dispatcher.on('adsGoogleExtension:setOrdersTIP', function ( e ) {

                if(typeof e.ordersDetail === 'undefined' || !e.ordersDetail.length){
                    var error = $('#msg-tip-error').text();
                    window.ADS.notify( error );
                    return;
                }

                e.ordersDetail.map(function ( orderDetail ) {

                    var trackingNo = orderDetail.trackingNo;

                    orderDetail.products.map(function ( product ) {
                        console.log(product);
                        var $row = $('.adsw-order_number[value="'+product.orderId+'"]').parent();
                        console.log($row);
                        var success = $('#msg-tip-success-all').text();

                        if($row.length){
                            $row.find('.adsw-tip').val(trackingNo);
                            $row.parents('tr').find('.adsw_tracking_id').hide(0)
                                .html('<a href="http://www.17track.net/?nums=' + trackingNo + '" target="_blank">' + trackingNo + '</a>')
                                .show(300);

                            window.ADS.notify( success );
                        }
                    })

                })

            }, this);

            function searchOrderId( productId, products ) {
                var foo = [];
                for (var k in products) {
                    var product = products[ k ];
                    if ( product && product[ 'productId' ] === productId ) {
                        foo.push(product[ 'id' ]);
                    }
                }

                return foo;
            }

            window.ADS.Dispatcher.on('adsGoogleExtension:setOrdersIdAli', function ( e ) {

                var products = e.products;

                e.ordersDetail.map(function ( orderDetail ) {

                    orderDetail.products.map(function ( product ) {

                        var orderId = product.orderId;

                        var orderIdStore = searchOrderId( product.productId, products );

                        var $row = $('[data-item_id="'+orderIdStore+'"]');

                        if($row.length){
                            $row.find('.adsw-order_number').val(orderId);
                            $row.parents('tr').find('.adsw_order_number').hide(0)
                                .html('<a href="http://trade.aliexpress.com/order_detail.htm?orderId=' + orderId + '" target="_blank">' + orderId + '</a>')
                                .show(300);

                        }
                    })

                })

            }, this);

        },
        init: function(){

            this.tip();

            var data = {
                score : {
                    url    : window.location.href,
                    name   : 'nameScore',
                    type   : 'addOrder',
                    plugin : 'woo'
                },
                items : {
                    th           : '.item-row-actions',
                    order_number : '.adsw_order_number',
                    tracking_id  : '.adsw_tracking_id'
                }
            };

            setTimeout( function () {
                window.postMessage( {
                    source : 'NAME_SOURCE_PAGE_ADD_ORDER',
                    action : 'initPageOrders',
                    info   : data
                }, "*" );
            }, 1000 );

            if( $(data.items.th).length ) {
                $(data.items.th).each(function(){
                    var order = $(this).find('.adsw-order_number').val(),
                        tracking = $(this).find('.adsw-tip').val();

                    var t = $(this).parents('tr'),
                        o = t.find(data.items.order_number),
                        d = t.find(data.items.tracking_id);

                    if( order )
                        o.html( '<a href="http://trade.aliexpress.com/order_detail.htm?orderId=' + order + '" target="_blank">' + order + '</a>' );
                    else
                        o.text(o.data('default'));

                    if( tracking )
                        d.html( '<a href="http://www.17track.net/?nums=' + tracking + '" target="_blank">' + tracking + '</a>' );
                    else{
                        if( order ) t.find('.get-tracking').show();
                        d.text(d.data('default'));
                    }
                });
            }

            $('#autofilling').on('click', '.button', function (  ) {

                var post_id = $(this).data('pid');

                $.ajaxQueue({
                    url: ajaxurl,
                    data: {action: 'adsw_placeOrderOnAli', post_id: post_id},
                    type: "POST",
                    dataType: 'json',
                    success: function (response){
                        data = response;
                        if(data){
                            window.postMessage( { source : 'NAME_SOURCE_PAGE_ADD_ORDER', action : 'addOrder', info : data }, "*" );
                        }
                    }
                });
            });
        }
    };
    autofilling.init();
});