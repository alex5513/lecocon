/**
 * Created by pavel on 02.06.2016.
 */
jQuery( function ( $ ) {

    if ( typeof window.ADS == 'undefined' ) {
        console.log( 'not ADS object' );
        return;
    }

    window.ADS.aliParseProduct = (function () {
        var $this;

        function htmlToObj( html ) {
            var div = $( '<div></div>' );
            return $( div ).append( html );
        }

        function getImagesObj( $obj ) {
            var data  = [];
            var $imgs = $obj.find( '.image-thumb-list li img' );
            if ( $imgs.length > 0 ) {
                $imgs.each( function ( i, e ) {
                    data[ i ]          = {};
                    data[ i ][ 'url' ] = $( this ).attr( 'src' ).replace( '_50x50.jpg', '' );
                    data[ i ][ 'alt' ] = $( this ).attr( 'alt' );
                } );
            } else {
                var $img = $obj.find( '.detail-gallery img' );
                if ( $img.length ) {
                    data[ 0 ]          = {};
                    data[ 0 ][ 'url' ] = $img.attr( 'src' ).replace( '_640x640.jpg', '' );
                    data[ 0 ][ 'alt' ] = $img.attr( 'alt' );
                }
            }

            return data;
        }

        function getSkuObj( $obj ) {
            var data = [];

            var $rows = $obj.find( '#j-product-info-sku dl' );
            $rows.each( function ( i, e ) {
                data[ i ]                = {};
                data[ i ][ 'sku-title' ] = $( this ).find( 'dt' ).text();

                var $items                 = $( this ).find( 'dd ul' );
                var $sku                   = $( this ).find( 'dd li:not(.sizing-info-item)' ).find( 'a' );
                data[ i ][ 'sku-prop-id' ] = $items.attr( 'data-sku-prop-id' );
                data[ i ][ 'sku-attr' ]    = [];
                $sku.each( function ( index, el ) {
                    var $attr         = {};
                    $attr[ 'sku-id' ] = $( this ).attr( 'data-sku-id' );
                    if ( $( 'img', this ).length ) {
                        $attr.title = $( 'img', this ).attr( 'title' );
                        $attr.img   = $( 'img', this ).attr( 'bigpic' );
                    } else if ( $( 'span', this ).length ) {
                        $attr.title = $( 'span', this ).text();
                        if ( $attr.title == '' ) {
                            $attr.title = $( 'span', this ).attr( 'title' );
                        }
                    }

                    if ( Object.keys( $attr ).length !== 0 )
                        data[ i ][ 'sku-attr' ].push( $attr );
                } );
            } );

            return data;
        }

        function getParamsObj( $obj ) {
            var data = [];

            var $rows = $obj.find( 'ul.product-property-list li' );
            $rows.each( function ( i, e ) {
                data[ i ]            = {};
                data[ i ][ 'name' ]  = $( this ).find( '.propery-title' ).text();
                data[ i ][ 'value' ] = $( this ).find( '.propery-des' ).text();
            } );

            return data;
        }

        function getPackagingObj( $obj ) {
            var data = [];

            var $rows = $obj.find( '.product-packaging-list:eq(0) li' );
            $rows.each( function ( i, e ) {
                data[ i ]            = {};
                data[ i ][ 'name' ]  = $( this ).find( '.packaging-title' ).text();
                data[ i ][ 'value' ] = $( this ).find( '.packaging-des' ).text();
            } );

            return data;
        }

        function getPrice( $obj ) {
            var skuProductsText = $obj.find( 'script:contains("skuProducts")' ).text();

            var minPrice    = skuProductsText.match( /minPrice="([0-9\.]+)";/im );
            var maxPrice    = skuProductsText.match( /maxPrice="([0-9\.]+)";/im );
            var actMinPrice = skuProductsText.match( /actMinPrice="([0-9\.]+)";/im );
            var actMaxPrice = skuProductsText.match( /actMaxPrice="([0-9\.]+)";/im );

            return {
                minPrice    : minPrice !== null ? minPrice[ 1 ] : '',
                maxPrice    : maxPrice !== null ? maxPrice[ 1 ] : '',
                actMinPrice : actMinPrice !== null ? actMinPrice[ 1 ] : '',
                actMaxPrice : actMaxPrice !== null ? actMaxPrice[ 1 ] : ''
            };
        }

        function getOffline( $obj ) {
            var skuProductsText = $obj.find( 'script:contains("skuProducts")' ).text();

            return skuProductsText.match( /offline="(\w+)";/im );
        }

        function getCurrencyCode( $obj ) {
            var skuProductsText = $obj.find( 'script:contains("skuProducts")' ).text();

            var currencyCode    = skuProductsText.match( /baseCurrencyCode="(\w+)";/im );

            return currencyCode !== null ? currencyCode[ 1 ] : '';
        }

        function getDescUrl( $obj ) {
            var skuProductsText = $obj.find( 'script:contains("descUrl")' ).text();

            var descUrl    = skuProductsText.match( /descUrl="(.+)";/im );
            //console.log( skuProductsText );
            return descUrl !== null ? descUrl[ 1 ] : '';
        }

        function getSkuProductsObj( $obj ) {
            var skuProductsText = $obj.find( 'script:contains("skuProducts")' );
            var skuProducts     = [];

            if ( !skuProductsText.length )return [];

            function skuProductsObj() {
                var JsonSkuProducts  = false;
                var text             = skuProductsText.text();
                var matchskuProducts = text.match( /skuProducts=(\[.*\]);/im );
                if ( matchskuProducts !== null ) {
                    JsonSkuProducts = ADS.tryJSON( matchskuProducts[ 1 ] );
                }
                return JsonSkuProducts;
            }

            var foo = skuProductsObj();
            if ( foo )
                for ( var i in foo ) {
                    var skuProduct = {
                        skuAttr : foo[ i ].skuAttr,
                        skuVal  : {
                            availQuantity : foo[ i ].skuVal.availQuantity,
                            inventory     : foo[ i ].skuVal.inventory,
                            isActivity    : foo[ i ].skuVal.isActivity,
                            actSkuPrice   : foo[ i ].skuVal.actSkuCalPrice,
                            skuPrice      : foo[ i ].skuVal.skuCalPrice

                        }
                    };
                    skuProducts.push( skuProduct );
                }

            return skuProducts;
        }

        function PackagingFormat( packaging ) {

            if ( !packaging.length )return {};
            return {
                'type'   : packaging[ 0 ][ 'value' ],
                'weight' : packaging[ 1 ][ 'value' ],
                'size'   : packaging[ 2 ][ 'value' ]
            };
        }

        function getID( linkProduct ) {
            var id = (/\/(\d+_)?(\d+)\.html/).exec( linkProduct );
            return id[ 2 ];
        }

        function parseParamsPageProduct( $obj, url ) {

            var data = {
                url                  : url,
                id                   : getID( url ),
                title                : '',
                description          : '',
                imgs                 : [],
                quantity             : '',
                wishlist             : '',
                shopLink             : '',
                shopLinkFeedback     : '',
                numberReviews        : '',
                shopName             : '',
                rankNum              : '',
                shopTime             : '',
                storeRank            : '',
                storeRankTitle       : '',
                storePositivePercent : '',
                sku                  : '',
                params               : '',
                packaging            : '',
                skuProducts          : {},
                prices               : {},
                starOrder            : {},
                lotNumeric           : '',
                orders               : '',
                feedbackUrl          : '',
                currencyCode         : '',
                descUrl		         : ''
            };

            data.lotNumeric  = $obj.find( '.p-unit-lot-disc' ).text().replace( /[^0-9]/, '' );
            data.title       = $obj.find( 'h1.product-name' ).text();
            data.description = $obj.find( '.description-content' ).html();
            data.starOrder   = {
                percent  : $obj.find( '.percent-num' ).text(),
                rantings : $obj.find( '.rantings-num' ).text()
            };

            data.quantity      = $obj.find( '[data-role="stock-num"]' ).text().replace( /[^0-9]/gim, '' );
            data.wishlist      = $obj.find( '.add-wishlist-action .wishlist-num' ).text().replace( /[^0-9]/gim, '' );
            data.numberReviews = $obj.find( '.main-content [data-trigger="feedback"] a' ).text().replace( /[^0-9]/gim, '' );
            data.orders        = $obj.find( '#j-order-num' ).text();

            data.feedbackUrl = $obj.find( 'iframe[thesrc^="//feedback.aliexpress.com/"]' ).attr( 'thesrc' );
            if ( typeof data.feedbackUrl == 'undefined' ) {
                data.feedbackUrl = $obj.find( 'iframe[src^="//feedback.aliexpress.com/"]' ).attr( 'src' );
            }

            data.imgs         = getImagesObj( $obj );
            data.sku          = getSkuObj( $obj );
            data.skuProducts  = getSkuProductsObj( $obj );
            data.prices       = getPrice( $obj );
            data.offline      = getOffline( $obj );
            data.currencyCode = getCurrencyCode( $obj );
            data.descUrl 	  = getDescUrl( $obj );
            data.params       = getParamsObj( $obj );
            data.packaging    = PackagingFormat( getPackagingObj( $obj ) );

            var $storeHeader          = $obj.find( '#j-store-header' );
            data.shopName             = $storeHeader.find( '.shop-name a' ).text();
            data.shopTime             = $storeHeader.find( '.shop-time' ).text();
            data.shopLink             = $storeHeader.find( '.shop-name a' ).attr( 'href' );
            data.rankNum              = $storeHeader.find( '.rank-num' ).text();
            data.storeRank            = $storeHeader.find( '.store-rank img' ).attr( 'src' );
            data.storeRankTitle       = $storeHeader.find( '.store-rank img' ).attr( 'title' );
            data.shopLinkFeedback     = $storeHeader.find( '.store-rank a' ).attr( 'href' );
            data.storePositivePercent = $storeHeader.find( '.positive-percent' ).text();

            return data;
        }

        return {
            init      : function () {
                $this = this;
            },
            parseHtml : function ( html, url ) {
                return $this.parseObj( htmlToObj( html ), url );
            },
            parseObj  : function ( Obj, url ) {
                return parseParamsPageProduct( Obj, url );
            }
        }
    })();

    window.ADS.aliParseProduct.init();

} );