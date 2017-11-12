jQuery( function ( $ ) {

    var registerHelpersHandlebars = {
        init : function () {
            Handlebars.registerHelper( 'pricesBlock', function ( prices, options ) {

                var idTmpl = 4;

                if ( prices[ 'actMinPrice' ] == '' &&
                    prices[ 'actMaxPrice' ] == '' &&
                    prices[ 'maxPrice' ] == prices[ 'minPrice' ] ) {
                    idTmpl = 1;
                }
                else if ( prices[ 'minPrice' ] == prices[ 'maxPrice' ] && prices[ 'actMinPrice' ] == prices[ 'actMaxPrice' ] ) {
                    idTmpl = 2;
                }
                else if ( prices[ 'maxPrice' ] != prices[ 'minPrice' ] && prices[ 'actMaxPrice' ] == '' && prices[ 'actMinPrice' ] == '' ) {
                    idTmpl = 3;
                }

                var tmpl     = $( '#tmpl-praces-' + idTmpl ).text(),
                    template = Handlebars.compile( tmpl );

                return template( prices );
            } );
        }
    };

    registerHelpersHandlebars.init();

    var ApplyFilterImport = {

        obj : {
            panelSearch : '#panel-search-results',
            categories  : $( '#categoryId' ),
            keywords    : $( '#keywords' ),
            apply       : $( '#selectiveImport' ),
            sort        : $( '#sort' ),
            layout      : $( '#listProducts' ),
            tmpl        : $( '#tmpl-listProducts' )
        },

        createPagination : function ( obj, total, current ) {

            var perPage = 20;

            current = parseInt( current );

            if ( total > 10000 ) total = 10000;

            obj.pagination( {
                items       : total,
                itemsOnPage : perPage,
                currentPage : current,
                cssStyle    : "light-theme",
                prevText    : obj.data( 'prev' ),
                nextText    : obj.data( 'next' ),

                onPageClick : function ( pageNumber ) {

                    ApplyFilterImport.request( pageNumber );
                }
            } );
        },

        checker : function () {

            var a = $( "#checkAll" ),
                l = $( '#listProducts' );

            a.change( function () {
                l.find( 'input:checkbox' ).prop( 'checked', $( this ).prop( "checked" ) );
            } );

            l.on( 'click', 'input:checkbox', function () {
                var u = l.find( "input:checkbox:not(:checked)" );

                if ( u.length && a.prop( "checked" ) ) {
                    a.prop( "checked", false );
                }
                else if ( u.length == 0 && !a.prop( "checked" ) ) {
                    a.prop( "checked", true );
                }
            } );
        },

        request : function ( page ) {

            var $ob   = this.obj,
                $tmpl = $ob.tmpl.html();

            if ( $.trim( $ob.keywords.val() ) == '' && $ob.categories.val() == '' ) {

                $ob.keywords.focus().parents( '.inputer' ).addClass( 'inputer-red' );

                ADS.notify( $( '#errorKeywords' ).html() );

                return false;
            }

            $.ajaxQueue( {
                url     : ajaxurl,
                data    : {
                    action : 'adsw_search_by_cat',
                    data   : $( '#js-adsimport-filter' ).serializeArray(),
                    page   : page
                },
                type    : "POST",
                success : function ( response ) {

                    var $layout = $ob.layout,
                        $paging = $layout.parent().find( '.pagination-menu' );

                    $layout.html( '' );
                    $paging.html( '' );

                    response = ADS.tryJSON( response );

                    if ( response && response.error != 'undefined' ) {

                        ApplyFilterImport.createPagination( $paging, response.totalResults, page );

                        $( '#total-find' ).text( response.totalResults );
                        
                        $.each( response.products, function ( i, p ) {
                            var price   = '',
                                percent = 0,
                                rate    = parseFloat( p.evaluateScore );

                            if ( rate > 0 )
                                percent = (rate / 0.05).toFixed( 2 );

                            if ( p.salePrice != p.originalPrice )
                                price = '<span class="originalPrice">' + p.originalPrice + '</span> ' +
                                    '<span class="discount">- ' + p.discount + '</span>';

                            if ( p.already ) {
                                p.btnClass = 'disabled';
                                p.btnIcon  = 'glyphicon-ok';
                                p.rowClass = 'import-success';
                            } else {
                                p.btnClass = '';
                                p.btnIcon  = 'glyphicon-plus';
                                p.rowClass = '';
                            }

                            var send = ADS.replace(
                                [ '%numb%', '%imageUrl%', '%productUrl%', '%productTitle%', '%salePrice%', '%packageType%', '%price%', '%percent%', '%volume%', '%rate%', '%btnClass%', '%btnIcon%', '%rowClass%' ],
                                [ i, p.imageUrl, p.productUrl, p.productTitle, p.salePrice, p.packageType, price, percent, p.volume, p.evaluateScore, p.btnClass, p.btnIcon, p.rowClass ],
                                $tmpl
                            );

                            $ob.layout.append( send );
                        } );
                        var panelSearch = ApplyFilterImport.obj.panelSearch;
                        if ( $( panelSearch ).is( ':hidden' ) ) {
                            $( panelSearch ).show( 500 );
                            ADS.scrollToNode( panelSearch );
                        }

                    }
                    else {
                        ADS.notify(response.error);
                    }
                }
            } );
        },

        waitText : function () {

            var $ob = this.obj;

            $ob.keywords.on( 'keypress', function () {

                var $inp = $( this ).parents( '.inputer' );

                if ( $inp.hasClass( 'inputer-red' ) )
                    $inp.removeClass( 'inputer-red' );
            } );
        },

        waitApply : function () {

            var $ob = this.obj;

            $ob.apply.on( 'click', function () {
                ApplyFilterImport.request( 1 );
            } );
        },

        waitSort : function () {

            var $ob = this.obj;

            $( $ob.panelSearch ).on( 'click', '.panel-heading [data-target]', function () {

                $( this ).parents( '.btn-group' ).find( '.dropdown-toggle span.name' ).text( $( this ).text() );

                $ob.sort.val( $( this ).data( 'target' ) );

                ApplyFilterImport.request( 1 );
            } );

        },

        init : function () {

            $( '[data-target="bulkImport"]' ).on( 'click', function ( e ) {

                var l     = $( '#listProducts' ),
                    items = l.find( 'input:checkbox:checked' );

                if ( items.length == 0 )
                    return false;

                items.each( function () {

                    var btn = $( this ).parents( '.product-item' ).find( '.js-import-product' );

                    if ( !btn.hasClass( 'disabled' ) )
                        btn.click();
                } );
            } );

            this.waitApply();
            this.waitSort();
            this.waitText();
            this.checker();
        }
    };

    ApplyFilterImport.init();

    var Base64 = {

        // private property
        _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode : function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = Base64._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

            }

            return output;
        },

        // public method for decoding
        decode : function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = Base64._utf8_decode(output);

            return output;

        },

        // private method for UTF-8 encoding
        _utf8_encode : function (string) {
            string = string.replace(/\r\n/g,"\n");
            var utftext = "";

            for (var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                }
                else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
                else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode : function (utftext) {
            var string = "";
            var i = 0;
            var c = c1 = c2 = 0;

            while ( i < utftext.length ) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                }
                else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                }
                else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }

    };

    var ProductAli = (function () {
        var $this;
        //event
        var $body        = $( "body" );
        //cache product
        var cacheProduct = [];

        var stack      = [];
        var _observers = {};
        var active     = false;

        var obj = {
            rows : '.product-item'
        };

        function b64DecodeUnicode( str ) {
            return Base64.decode( str );
            //return decodeURIComponent( escape( atob( str ) ) );
        }

        function getParamsPageProduct2( e ) {
            var linkProduct =  e.index.linkProduct;
            var product     = e.index.product;

            var desc    = e.html.match( /window.productDescription='(.+)';/im );

            product.description = desc !== null ? desc[ 1 ] : '';

            var url         = linkProduct.replace( 'item/a/', 'item//' );
            $( obj.rows + '[data-url="' + url + '"]' ).data( 'json', product );
            $body.trigger( {
                type    : "params:product",
                product : product,
                url     : linkProduct
            } );
        }

        function getParamsPageProduct( e ) {
            var $obj        = e.obj,
                linkProduct = e.url;
            var product     = window.ADS.aliParseProduct.parseObj( $obj, linkProduct );
            window.ADS.aliExpansion.addTask( 'https:'+product.descUrl, getParamsPageProduct2, this, {linkProduct:linkProduct,product:product} );
        }

        function getDataProduct( linkProduct ) {
            //todo backend replace url
            var url = linkProduct.replace( 'item/a/', 'item//' );
            return $( obj.rows + '[data-url="' + url + '"]' ).data( 'json' );
        }

        function addStack( link, observer, context ) {
            context = context || null;

            if ( typeof _observers[ link ] == 'undefined' )_observers[ link ] = [];

            _observers[ link ].push( { observer : observer, context : context } );
            stack.push( link );
        }

        function getStack() {
            return stack.pop();
        }

        function notify( link, data ) {
            if ( Object.keys( _observers ).length ) {

                var cb = _observers[ link ],
                    i;

                for ( i in cb ) {
                    var item = cb[ i ];
                    item.observer.call( item.context, data );
                }
                delete _observers[ link ];
            }
        }

        function getProductInfo( linkProduct ) {

            var json = getDataProduct( linkProduct );

            if ( json ) {
                $body.trigger( {
                    type    : "params:product",
                    product : json,
                    url     : linkProduct
                } );
            } else {
                //todo backend replace url
                linkProduct = linkProduct.replace( 'item//', 'item/a/' );
                window.ADS.aliExpansion.addTask( linkProduct, getParamsPageProduct, this, linkProduct );
            }
        }

        return {
            init    : function () {
                $this = this;
                window.addEventListener( "message", function ( event ) {

                    if ( event.source != window )
                        return;

                    if ( event.data.type && (event.data.type == "contentProductAli") ) {
                        event.data.info.html = b64DecodeUnicode( event.data.info.html );
                        var product          = window.ADS.aliParseProduct.parseHtml( event.data.info.html, event.data.info.url );

                        $body.trigger( {
                            type    : "params:importSendAli",
                            product : product
                        } );
                    }
                }, false );

                $body.on( 'params:product', function ( e ) {

                    notify( e.url, e.product );


                    linkProduct = getStack();
                    if ( linkProduct ) {
                        getProductInfo( linkProduct );
                    } else {
                        active = false;
                    }

                } );

                window.postMessage( {
                    type : "initPagesImport",
                    name : store.name
                }, "*" );

            },
            addTask : function ( link, observer, context ) {
                addStack( link, observer, context );
                if ( !active ) {
                    active = true;
                    getProductInfo( link );
                }
            }
        }
    })();

//    ProductAli.init();

    var ImportProduct = (function () {
        var $this;
        var $body = $( "body" );
        var obj   = {
            rows     : '.product-item',
            btn      : '.js-import-product',
            btnModal : '.js-modal-add-to-import-list'

        };

        return {
            init : function () {
                $this = this;

                $body.on( 'click', obj.btn, function () {
                    var linkProduct = $( this ).closest( obj.rows ).data( 'url' );

                    //todo backend replace url
                    linkProduct = linkProduct.replace( 'item//', 'item/a/' );
                    $( this ).addClass( 'disabled' ).find( 'span' ).addClass( 'infinite' );
                    ProductAli.addTask( linkProduct, ImportProduct.push );
                } );

                $body.on( 'click', obj.btnModal, function () {
                    var linkProduct = $( this ).closest( '.modal-content' ).find( '.product-supplier' ).data( 'url' );
                    //todo backend replace url
                    linkProduct     = linkProduct.replace( 'item//', 'item/a/' );
                    $( this ).addClass( 'disabled' ).find( 'span' ).addClass( 'infinite' );
                    ProductAli.addTask( linkProduct, ImportProduct.push );
                } );

                /*$body.on( 'params:importSendAli', function ( e ) {
                    ImportProduct.push( e.product );
                } );*/

            },
            push : function ( product ) {

                var id = product.id;

                    product.countries = '';

                    $.ajaxQueue( {
                        url      : ajaxurl,
                        dataType : 'json',
                        data     : {
                            action  : 'adsw_product_ali',
                            product : ADS.b64EncodeUnicode( JSON.stringify( product ) )
                        },
                        type     : "POST",
                        success  : function ( response ) {

                            if( response.hasOwnProperty('error') ) {

                                $( obj.rows + '[data-url*="' + id + '"]' )
                                    .addClass( 'import-success' )
                                    .find( obj.btn )
                                    .addClass( 'disabled' )
                                    .find( 'span.glyphicon' )
                                    .removeClass( 'infinite glyphicon-plus' )
                                    .addClass( 'glyphicon-ok disabled' );
                                $( '.product-supplier[data-url*="' + id + '"]' ).closest( '.modal-content' ).find( obj.btnModal + ' span.glyphicon' )
                                    .removeClass( 'infinite glyphicon-plus' )
                                    .addClass( 'glyphicon-ok' );

                                $body.trigger( {
                                    type : "alids:importProduct",
                                    id   : response.id
                                } );
                            } else {
                                $this.images( response );
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

                            $( obj.rows + '[data-url*="' + response.url + '"]' )
                                .addClass( 'import-success' )
                                .find( obj.btn )
                                .addClass( 'disabled' )
                                .find( 'span.glyphicon' )
                                .removeClass( 'infinite glyphicon-plus' )
                                .addClass( 'glyphicon-ok disabled' );
                            $( '.product-supplier[data-url*="' + response.url + '"]' ).closest( '.modal-content' ).find( obj.btnModal + ' span.glyphicon' )
                                .removeClass( 'infinite glyphicon-plus' )
                                .addClass( 'glyphicon-ok' );

                            $body.trigger( {
                                type : "alids:importProduct",
                                id   : response.id
                            } );
                        } else {
                            $this.images( response.product );
                        }
                    }
                } );
            }
        }

    })();

    ImportProduct.init();

    var SupplierInfo = (function () {
        var $this;
        var $body          = $( "body" );
        var $modal         = $( '#panel-modal' );
        var $tmplBodyModal = $( '#tmpl-bodyModal' ).text();
        var obj            = {
            rows         : '.product-item',
            supplierInfo : '.js-supplier-info',
            btnGoAli     : '.js-modal-go-to-aliexpress',
            slider       : {
                item    : '.thumb-list li',
                img     : '.img-main img',
                imgSku  : '.product-supplier .sku-wrap-img img',
                itemSku : '.sku-wrap-img'
            }
        };

        function modalRender( product ) {
            var $html = templateModal( product );
            modalHtml( $html );
        }

        function templateModal( data ) {
            $html = ADS.objTotmpl( $tmplBodyModal, data );
            return $html;
        }

        function modalHtml( $html ) {
            $modal.find( '.modal-body' ).html( $html );
        }

        function initZoom() {
            $( 'body img#zoom' ).elevateZoom( {
                zoomType           : "lens",
                lensShape          : "round",
                borderSize         : 8,
                cursor             : 'crosshair',
                responsive         : true,
                containLensZoom    : true,
                lensSize           : 150,
                borderColour       : 'rgba(193,193,193,0.3)',
                zoomWindowFadeIn   : 500,
                zoomWindowFadeOut  : 750,
                zoomWindowWidth    : 200,
                zoomWindowHeight   : 200,
                gallery            : 'list-thumb',
                galleryActiveClass : 'active'
            } );

            activeZoom();
        }


        function initSlick() {
            $( '.thumb-list' ).slick( {
                slidesToShow   : 4,
                slidesToScroll : 1,
                autoplay       : true,
                autoplaySpeed  : 2000,
                vertical       : true
            } );
        }

        function activeZoom() {
            var gallery = $( '.thumb-list' );
            setTimeout( function () {
                gallery.find( '.item-touch.active' ).click();
            }, 400 );
        }

        function destroyZoom(  ) {
            $('.zoomContainer').remove();
            $( 'body img#zoom' ).removeData('elevateZoom').removeData('zoomImage');
        }

        return {
            showProduct : function ( product ) {
                var m = $modal.find( '.js-modal-add-to-import-list' );
                if ( $( obj.rows + '[data-url*="' + product.id + '"]' ).hasClass( 'import-success' ) ) {
                    m.addClass( 'disabled' ).find( 'span.glyphicon' ).removeClass( 'glyphicon-plus' ).addClass( 'glyphicon-ok' );
                }
                else {
                    m.removeClass( 'disabled' ).find( 'span.glyphicon' ).removeClass( 'glyphicon-ok' ).addClass( 'glyphicon-plus' );
                }

                    product[ 'starRating' ] = parseFloat( product.starOrder.percent ) * 20 + '%';

                    modalRender( product );

                    $modal.modal( 'show' );
                    $body.trigger( {
                        type    : "modal:show",
                        product : product
                    } );

            },
            init        : function () {
                $this = this;

                $body.on( 'modal:show', function () {
                    initZoom();
                    initSlick();
                } );

                $( window ).resize( function () {
                    activeZoom();
                } );

                $body.on( 'click', obj.supplierInfo, function () {
                    var linkProduct = $( this ).closest( obj.rows ).data( 'url' );
                    linkProduct     = linkProduct.replace( 'item//', 'item/a/' );
                    ProductAli.addTask( linkProduct, SupplierInfo.showProduct );
                    if ( !$( this ).closest( obj.rows ).data( 'json' ) ) {
                        $body.trigger( {
                            type : "modal:load",
                            link : linkProduct
                        } );
                    }
                } );

                $modal.on( 'hidden.bs.modal', function ( e ) {
                    destroyZoom();

                } );

                $body.on( 'click', obj.slider.item + ',' + obj.slider.itemSku, function () {
                    $( obj.slider.item + ',' + obj.slider.itemSku ).removeClass( 'active' );
                    var src = $( this ).addClass( 'active' ).find( 'img' ).attr( 'src' );
                    $( obj.slider.img ).attr( 'src', src );
                    $( obj.slider.img ).attr( 'data-zoom-image', src );

                    var ez = $( 'img#zoom' ).data( 'elevateZoom' );
                    ez.swaptheimage( src, src );
                } );

                $body.on( 'click', obj.btnGoAli, function () {
                    var linkProduct = $( this ).closest( '.modal-content' ).find( '.product-supplier' ).data( 'url' );
                    //togo backend replace url
                    linkProduct     = linkProduct.replace( 'item//', 'item/a/' );
                    window.open( linkProduct, '_blank' );
                } );

            }
        }

    })();

    SupplierInfo.init();

    var cover = {
        init : function () {
            $( 'body' ).on( 'modal:show', function () {
                setTimeout( function () {
                    ADS.coverHide()
                }, 1000 );
            } );
            $( 'body' ).on( 'modal:load', function () {
                ADS.coverShow();
            } );
        }
    };
    cover.init();

    var AvailableExtensions = (function () {
        var $this          = this;
        var is_chrome      = false,
            chrome_version = false,
            active         = true;
        if ( navigator.userAgent.toLowerCase().indexOf( 'chrome' ) > -1 ) {
            is_chrome      = true;
            chrome_version = navigator.userAgent.replace( /^.*Chrome\/([\d\.]+).*$/i, '$1' )
        }

        var template = {
            alertBrowser   : $( '#tmpl-alertBrowser' ),
            alertExpansion : $( '#tmpl-alertExpansion' )
        };

        var obj = {
            form : $( '#js-adsimport-filter' )
        };

        return {

            init             : function () {
                $this = this;
                if ( !is_chrome ) {
                    active = false;
                    $this.showAlertBrowser();
                    $this.disablePagesImport()
                }

                var tim      = 0;
                var interval = setInterval( function () {
                    tim++;

                    if ( $this.is() ) {
                        ADS.coverHide();
                        clearInterval( interval );
                        ProductAli.init();
                    } else {
                        ADS.coverShow();
                    }

                    if ( tim > 10 && !$this.is() ) {
                        clearInterval( interval );
                        ADS.coverHide();
                        var tmpl = template.alertExpansion.html();
                        obj.form.html( tmpl );
                    }
                }, 200 );

            },
            is               : function () {
                return ( $( 'body' ).hasClass( 'expansion-alids-init' ) );
            },
            showAlertBrowser : function () {
                var tmpl = template.alertBrowser.html();
                obj.form.html( tmpl );

            },
            activeImport     : function () {
                return active;
            }
        }

    })();

    AvailableExtensions.init();
});