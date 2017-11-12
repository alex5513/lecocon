/**
 * Created by pavel on 30.05.2016.
 */
jQuery( function ( $ ) {

    if ( typeof window.ADS == 'undefined' ) {
        console.log( 'not ADS object' );
        return;
    }

    window.ADS.aliExpansion = (function () {
        var $this;
        var $body = $('body');

        var queue = {
            active : false,
            stack : []
        };

        var options = {
            sleep : 5000
        };

        var stageLoderPages = {
            active : false,
            stack : [],
            _observers : []
        };

        function htmlToObj( html ) {
            var div = $( '<div></div>' );
            return $( div ).append( html );
        }

        function b64EncodeUnicode( str ) {
            return btoa( encodeURIComponent( str ).replace( /%([0-9A-F]{2})/g, function ( match, p1 ) {
                return String.fromCharCode( '0x' + p1 );
            } ) );
        }

        function getPage( link ) {
            window.postMessage( { type : "requestHtml", url : link }, "*" );
        }

        function b64DecodeUnicode( str ) {
            return decodeURIComponent( escape( atob( str ) ) );
        }


        function addStack( link, observer, context, index ) {
            var context = context || null;
            if ( typeof stageLoderPages._observers[ link ] == 'undefined' )stageLoderPages._observers[ link ] = [];
            stageLoderPages._observers[ link ].push( { observer : observer, context : context, index : index } );
            stageLoderPages.stack.push( link );
            //console.log( 'stack:add', link );
        }

        function getStack() {
            return stageLoderPages.stack.pop();
        }

        function notify( link, data ) {
            if ( Object.keys( stageLoderPages._observers ).length ) {
                //console.log( 'stack:link', link );
                //console.log( 'stack:cb', stageLoderPages._observers );
                var cb = stageLoderPages._observers[ link ],
                    i;

                for ( i in cb ) {
                    var item        = cb[ i ];
                    data[ 'index' ] = item.index;
                    item.observer.call( item.context, data );
                }
                delete stageLoderPages._observers[ link ];
            }
        }

        /**
         * отправляет  background Dispatcher.on('action', response.info)
         * @param action
         * @param info
         */
        function sendToBg( action, info ) {
            info = info || {};

            window.postMessage( {
                source : 'NAME_SOURCE_BG',
                action : action,
                info   : info
            }, "*" );
        }

        function images ( product ) {

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

                        sendToBg('toBg:resultPublishProduct', {
                            product: {id : response.id, title: response.title},
                            response : {error: response.success, success: response.post_id }
                        });

                        $body.trigger({ type : 'alids:importSuccess' });
                        console.log(113);
                    } else {

                        images( response.product );

                        sendToBg('toBg:resultPublishProduct', {
                            product: {id : response.product.id},
                            response : {error: response.product.message }
                        });
                    }
                }
            } );
        }

        function importProduct( productOriginal ) {

            var form_Data     = new FormData();

            form_Data.append('action', 'adsw_product_ali');
            form_Data.append('product', ADS.b64EncodeUnicode( JSON.stringify( productOriginal ) ) );

            $.ajaxQueue( {
                url      : ajaxurl,
                dataType : 'json',
                data     : form_Data,
                contentType:false,
                processData:false,
                type     : "POST",
                success  : function ( response ) {

                    if( response.hasOwnProperty('error') ) {

                        sendToBg('toBg:resultPublishProduct', {
                            product: productOriginal,
                            response : response
                        });

                        $body.trigger({ type : 'alids:importSuccess' });
                        console.log(151);
                    } else {
                        images( response );
                    }
                }
            } );
        }

        function publicProduct( info ) {
            var product  = info.product;

            var form_Data     = new FormData();

            //console.log( info );

            form_Data.append('action', 'adsw_google_extension');
            form_Data.append('ads_actions', 'publicProduct');
            form_Data.append('product_id', product.product_id);


            for ( var key in info ) {
                var value = info[key];

                if(typeof value == 'object')
                    value = JSON.stringify(value);

                form_Data.append(key, value);
            }

            $.ajaxQueue( {
                url      : ajaxurl,
                dataType : 'json',
                data     : form_Data,
                contentType:false,
                processData:false,
                type     : "POST",
                success  : function ( response ) {

                    if( response.hasOwnProperty('error') ) {
                        window.postMessage( {
                            type     : "alids:importProduct",
                            product  : product,
                            response : response
                        }, "*" );
                        sendToBg('toBg:resultPublishProduct', {
                            product: product,
                            response : response
                        });

                    } else {
                        images( response );
                    }
                }
            } );
        }

        function eventAdsGoogleExtension( data ) {
            var info          = data.info;
            var dataInActions = info.data;
            var form_Data     = new FormData();

            //console.log( info );

            form_Data.append('action', 'adsw_google_extension');
            form_Data.append('ads_actions', info.ads_actions);

            if(typeof info.productId != 'undefined'){
                form_Data.append('product_id', info.productId);
            }

            for ( var key in dataInActions ) {
                var value = dataInActions[key];

                if(typeof value == 'object')
                    value = JSON.stringify(value);

                form_Data.append(key, value);
            }

            window.ADS.Dispatcher.trigger("adsGoogleExtension:"+info.ads_actions, dataInActions);

            $.ajaxQueue( {
                url      : ajaxurl,
                data     : form_Data,
                contentType:false,
                processData:false,
                type     : "POST",
                success  : function ( response ) {
                    response = ADS.tryJSON( response );

                    if( response && response.hasOwnProperty('done') ){
                        window.postMessage( {
                            type         : "adsGoogleExtension:toBg",
                            data         : {
                                ads_actions : info.ads_actions,
                                callback    : info.callback,
                                response    : response
                            },
                            tabId        : data.tabId

                        }, "*" );
                        return;
                    }

                    window.postMessage( {
                        type         : "adsGoogleExtension:toBg",
                        data         : {
                            ads_actions : info.ads_actions,
                            callback    : info.callback,
                            response    : { error : 'not response' }
                        },
                        tabId        : data.tabId

                    }, "*" );

                },
                error    : function () {
                    window.postMessage( {
                        type         : "adsGoogleExtension:toBg",
                        data         : {
                            ads_actions : info.ads_actions,
                            callback    : info.callback,
                            response    : { error : 'error send' }
                        },
                        tabId        : data.tabId

                    }, "*" );
                },
            });

        }

        var sendActivate = function () {

            $.ajaxQueue( {
                url      : ajaxurl,
                data     : {
                    action : 'adsw_google_extension',
                    ads_actions : 'infoShop'
                },
                type     : "POST",
                success  : function ( response ) {
                    response = ADS.tryJSON( response );

                    window.postMessage( {
                        type : "initAliExpansion",
                        name : store.name,
                        shop : response
                    }, "*" );

                    sendToBg('toBg:initAliExpansion',{
                        store  : {
                            name            : store.name,
                            url             : window.location.origin,
                            linkImportStore : window.location.href
                        },
                        shop : response
                    });

                }
            });

        };

        /**
         * Отправляет сообщения в модуль activeStore
         * @param action
         * @param info
         */
        //TODO заменить на sendToBg
        function sendToaliExpansion( action, info ) {

            info = info || {};

            window.postMessage( {
                type         : "adsGoogleExtension:toBg",
                data         : {
                    action  : action,
                    info    : info

                }
            }, "*" );
        }

        return {
            init    : function () {
                $this = this;
                window.addEventListener( "message", function ( event ) {

                    if ( event.source != window )
                        return;

                    if ( !event.data.type )
                        return;

                    if ( event.data.type == "responseHtml" ) {
                        event.data.info.html = b64DecodeUnicode( event.data.info.html );
                        event.data.info.obj  = htmlToObj( event.data.info.html );

                        notify( event.data.info.url, event.data.info );

                        setTimeout(function (  ) {
                            var linkPages = getStack();
                            if ( linkPages ) {
                                getPage( linkPages );
                            } else {
                                stageLoderPages.active = false;
                            }
                        }, options.sleep);

                    }

                    if ( event.data.type == "adsGoogleExtension:toShop" ) {
                        eventAdsGoogleExtension( event.data.info );
                    }

                    if ( event.data.type == "adsGoogleExtension:parseProductPage" ) {
                        event.data.info.html = b64DecodeUnicode( event.data.info.html );
                        product              = window.ADS.aliParseProduct.parseHtml( event.data.info.html, event.data.info.url );

                        product['post_status']  ='';

                        $.ajaxQueue( {
                            url      : ajaxurl,
                            data     : {
                                action : 'adsw_google_extension',
                                ads_actions : 'generatePermalink',
                                title : product.title
                            },
                            type     : "POST",
                            success  : function ( response ) {
                                response = ADS.tryJSON( response );
                                product.permalink = response.permalink;

                                sendToBg( 'parseProductAli:done', {
                                    product : product,
                                    url     : event.data.info.url
                                } );
                            }
                        });

                    }

                    if ( event.data.type == "adsGoogleExtension:PublicProductHtml") {
                        event.data.info.html = b64DecodeUnicode( event.data.info.html );
                        product              = window.ADS.aliParseProduct.parseHtml( event.data.info.html, event.data.info.url );
                        importProduct( product );
                    }

                    if ( event.data.type == "adsGoogleExtension:PublicProductObj") {

                        $this.addQueue(event.data.info.product);
                    }

                    if ( event.data.type && (event.data.type == "adsGoogleExtension:PublicProduct") ) {
                        publicProduct(event.data.info)
                    }

                }, false );

                $( 'body' ).on( 'test:extensions', function ( e ) {
                    if ( e.active ) {
                        sendActivate();
                    }
                } );

                $( 'body' ).on( 'alids:importSuccess', function () {
console.log(queue);
                    if( queue.stack.length )
                        importProduct( queue.stack.pop() );
                    else
                        queue.active = false;
                } );

            },
            addQueue : function( product ) {

                queue.stack.push( product );

                if( ! queue.active ) {
                    queue.active = true;
                    importProduct( queue.stack.pop() );
                }
            },
            addTask : function ( link, observer, context, index ) {
                addStack( link, observer, context, index );
                if ( !stageLoderPages.active ) {
                    stageLoderPages.active = true;
                    getPage( link );
                }
            },
            sleepTask: function(time){
                options.sleep = time * 1000;
                return 'set sleep - ' + time + 'sec';
            },
            /**
             * Получает трекинги по номерам заказа на Али и
             * записывает их всем товарам из заказа
             *
             * @param  {String[] || String} ordersIdAli
             * */
            getTIPOrders: function( ordersIdAli ){
                sendToaliExpansion( 'getTIPOrders', ordersIdAli );
            },
            /**
             * Получает трекинг по номеру заказа на Али и
             * записывает товару с orderIdStore
             *
             * @param  {String | Integer} orderIdStore
             * @param  {String} orderIdAli
             *
             * */
            getTIPOrder: function( orderIdStore, orderIdAli ){
                sendToaliExpansion( 'getTIPOrder', {
                    orderIdStore : orderIdStore,
                    orderIdAli   : orderIdAli
                } )
            },
            /*
             * Получает все трекинги с Али и
             * записывает их всем товарам из заказа
             * */
            getTIPAllOrders: function(){
                sendToaliExpansion( 'getTIPAllOrders' )
            },

            /**
             * ручное размещение заказа
             * расширение создает вкладку и вней отслеживаает покупку
             *
             * @return event adsGoogleExtension:setOrdersIdAli
             */
            placeOrderManually: function ( product_url, product_id, item_id ) {
                sendToaliExpansion( 'placeOrderManually', {
                    product_url : product_url,
                    product_id : product_id,
                    orderIdStore: item_id
                } );
            },
            /**
             * получает html страницы вызывается событие adsGoogleExtension:getPageHtml:Done"
             * @param url
             * @param params
             */
            getPageHtml: function(url, params){
                sendToaliExpansion( 'getPageHtml', {
                    url : url,
                    params : params
                } );
            }
        }
    })();

    window.ADS.aliExpansion.init();

} );