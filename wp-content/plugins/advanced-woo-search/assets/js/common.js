(function($){
    "use strict";

    var selector = '.aws-container';
    var instance = 0;
    var pluginPfx = 'aws_opts';
    var translate = {
        sale      : aws_vars.sale,
        noresults : aws_vars.noresults
    };

    $.fn.aws_search = function( options ) {

        var methods = {

            init: function() {

                $('body').append('<div id="aws-search-result-' + instance + '" class="aws-search-result" style="display: none;"></div>');

                setTimeout(function() { methods.resultLayout(); }, 500);

            },

            onKeyup: function(e) {

                searchFor = $searchField.val();
                searchFor = searchFor.trim();
                searchFor = searchFor.replace( /<>\{\}\[\]\\\/]/gi, '' );
                searchFor = searchFor.replace( /\s\s+/g, ' ' );

                for ( var i = 0; i < requests.length; i++ ) {
                    requests[i].abort();
                }

                if ( searchFor === '' ) {
                    $(d.resultBlock).html('').hide();
                    methods.hideLoader();
                    return;
                }

                if ( typeof cachedResponse[searchFor] != 'undefined') {
                    methods.showResults( cachedResponse[searchFor] );
                    return;
                }

                if ( searchFor.length < d.minChars ) {
                    $(d.resultBlock).html('');
                    methods.hideLoader();
                    return;
                }

                if ( d.showLoader ) {
                    methods.showLoader();
                }

                var data = {
                    action: 'aws_action',
                    keyword : searchFor,
                    page: 0
                };

                requests.push(

                    $.ajax({
                        type: 'POST',
                        url: ajaxUrl,
                        data: data,
                        success: function( response ) {

                            var response = $.parseJSON( response );

                            cachedResponse[searchFor] = response;

                            methods.showResults( response );

                            methods.showResultsBlock();

                            methods.analytics( searchFor );

                        },
                        error: function (data, dummy) {
                        }
                    })

                );

            },

            showResults: function( response ) {

                var html = '<ul>';


                if ( ( typeof response.cats !== 'undefined' ) && response.cats.length > 0 ) {

                    $.each(response.cats, function (i, result) {

                        html += '<li class="aws_result_item aws_result_cat">';
                        html += '<a class="aws_result_link" href="' + result.link + '" >';
                        html += '<span class="aws_result_content">';
                        html += '<span class="aws_result_title">';
                        html += result.name + ' (' + result.count + ')';
                        html += '</span>';
                        html += '</span>';
                        html += '</a>';
                        html += '</li>';

                    });

                }

                if ( ( typeof response.tags !== 'undefined' ) && response.tags.length > 0 ) {

                    $.each(response.tags, function (i, result) {

                        html += '<li class="aws_result_item aws_result_tag">';
                        html += '<a class="aws_result_link" href="' + result.link + '" >';
                        html += '<span class="aws_result_content">';
                        html += '<span class="aws_result_title">';
                        html += result.name + ' (' + result.count + ')';
                        html += '</span>';
                        html += '</span>';
                        html += '</a>';
                        html += '</li>';

                    });

                }

                if ( ( typeof response.products !== 'undefined' ) && response.products.length > 0 ) {

                    $.each(response.products, function (i, result) {

                        html += '<li class="aws_result_item">';
                        html += '<a class="aws_result_link" href="' + result.link + '" >';

                        if ( result.image ) {
                            html += '<span class="aws_result_image">';
                            html += '<img src="' + result.image + '">';
                            html += '</span>';
                        }

                        html += '<span class="aws_result_content">';
                        html += '<span class="aws_result_title">' + result.title + '</span>';
                        
                        if ( result.stock_status ) {
                            var statusClass = result.stock_status.status ? 'in' : 'out';
                            html += '<span class="aws_result_stock ' + statusClass + '">';
                                html += result.stock_status.text;
                            html += '</span>';
                        }

                        if ( result.sku ) {
                            html += '<span class="aws_result_sku">SKU: ' + result.sku + '</span>';
                        }

                        if ( result.excerpt ) {
                            html += '<span class="aws_result_excerpt">' + result.excerpt + '</span>';
                        }

                        if ( result.price ) {
                            html += '<span class="aws_result_price">' + result.price + '</span>';
                        }

                        html += '</span>';

                        if ( result.on_sale ) {
                            html += '<span class="aws_result_sale">';
                            html += '<span class="aws_onsale">' + translate.sale + '</span>';
                            html += '</span>';
                        }

                        html += '</a>';
                        html += '</li>';

                    });

                    //html += '<li class="aws_result_item aws_search_more"><a href="' + opts.siteUrl + '/?s=' + searchFor + '&post_type=product">View all</a></li>';
                    //html += '<li class="aws_result_item"><a href="#">Next Page</a></li>';

                }

                if ( ( typeof response.cats !== 'undefined' ) && response.cats.length <= 0 && ( typeof response.tags !== 'undefined' ) && response.tags.length <= 0 && ( typeof response.products !== 'undefined' ) && response.products.length <= 0 ) {
                    html += '<li class="aws_result_item aws_no_result">' + translate.noresults + '</li>';
                }


                html += '</ul>';

                methods.hideLoader();

                $(d.resultBlock).html( html );

                methods.showResultsBlock();

            },

            showResultsBlock: function() {
                methods.resultLayout();
                $(d.resultBlock).show();
            },

            showLoader: function() {
                $searchForm.addClass('processing');
            },

            hideLoader: function() {
                $searchForm.removeClass('processing');
            },

            onFocus: function( event ) {
                if ( searchFor !== '' ) {
                    methods.showResultsBlock();
                }
            },

            hideResults: function( event ) {
                if ( ! $(event.target).closest( ".aws-container" ).length ) {
                    $(d.resultBlock).hide();
                }
            },

            resultLayout: function () {
                var offset = self.offset();

                if ( offset ) {

                    var width = self.outerWidth();
                    var top = offset.top + $(self).innerHeight();
                    var left = offset.left;

                    $( d.resultBlock ).css({
                        width : width,
                        top : top,
                        left: left
                    });

                }

            },

            analytics: function( label ) {
                if ( d.useAnalytics ) {
                    try {
                        ga('send', 'event', 'AWS search', 'AWS Search Term', label);
                    }
                    catch (error) {
                    }
                }
            }

        };


        var self           = $(this),
            $searchForm    = self.find('.aws-search-form'),
            $searchField   = self.find('.aws-search-field'),
            haveResults    = false,
            requests       = Array(),
            searchFor      = '',
            cachedResponse = new Array();


        var ajaxUrl = ( self.data('url') !== undefined ) ? self.data('url') : false;


        if ( options === 'relayout' ) {
            var d = self.data(pluginPfx);
            methods.resultLayout();
            return;
        }


        instance++;

        self.data( pluginPfx, {
            minChars  : ( self.data('min-chars')   !== undefined ) ? self.data('min-chars') : 1,
            showLoader: ( self.data('show-loader') !== undefined ) ? self.data('show-loader') : true,
            showPage: ( self.data('show-page') !== undefined ) ? self.data('show-page') : true,
            useAnalytics: ( self.data('use-analytics') !== undefined ) ? self.data('use-analytics') : false,
            instance: instance,
            resultBlock: '#aws-search-result-' + instance
        });


        var d = self.data(pluginPfx);



        if ( $searchForm.length > 0 ) {
            methods.init.call(this);
        }


        $searchField.on( 'keyup', function(e) {
            methods.onKeyup(e);
        });


        $searchField.on( 'focus', function (e) {
            methods.onFocus(e);
        });


        $searchForm.on( 'keypress', function(e) {
            if ( e.keyCode == 13 && ! d.showPage ) {
                e.preventDefault();
            }
        });


        $(document).on( 'click', function (e) {
            methods.hideResults(e);
        });


        $(window).on( 'resize', function(e) {
            methods.resultLayout();
        });


        $(window).on( 'scroll', function(e) {
            if ( $( d.resultBlock ).css('display') == 'block' ) {
                methods.resultLayout();
            }
        });

    };


    // Call plugin method
    $(document).ready(function() {

        $(selector).each( function() {
            $(this).aws_search();
        });

    });


})( jQuery );