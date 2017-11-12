/**
 * Created by Vitaly Kukin on 29.03.2016.
 */
jQuery(function($) {

    Pleasure.init();
    Layout.init();

    $(document).on( 'change keyup', 'input', function () {

        var type = [ 'submit', 'radio', 'checkbox' ];

        if ( $( this ).val() != '' ) $( document ).find( 'input' ).each( function () {
            if ( $.inArray( $( this ).attr( 'type' ), type ) == -1 &&
                $( this ).val() != '' &&
                $( this ).next().length > 0 &&
                $( this ).next().prop( 'tagName' ) == 'LABEL' )
                $( this ).addClass( 'valid' );
        } );
    });

    var app = {

        objTotmpl : function ( tmpl, data ) {
            if ( typeof Handlebars === 'undefined' ) {
                console.log( 'Handlebars not registry' );
                return false
            }

            var template = Handlebars.compile( tmpl );
            return template( data );
        }
    };

    var innerPost = (function () {

        var obj = {
            row     : '#tmpl-row-edit',
            img     : '#tmpl-item-media',
            gallery : '#ads-gallery',
            sku     : '#ads-sku'
        };

        var status = false;

        function renderMediaUploader() {

            var file_frame;

            if ( undefined !== file_frame ) {
                file_frame.open();
                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media( {
                frame    : 'post',
                state    : 'insert',
                multiple : true
            } );

            file_frame.on( 'insert', function () {

                file_frame.state().get( 'selection' ).each( function ( image ) {

                    if ( !checkExistingId( image.id ) ) {
                        $.ajax( {
                            url     : ajaxurl,
                            data    : {
                                action : 'adsw_get_image',
                                id     : image.id,
                                size   : 'medium'
                            },
                            type    : "POST",
                            success : function ( response ) {

                                response = { id : image.id, url : response };
                                $( obj.gallery ).append( app.objTotmpl( $( obj.img ).html(), response ) )
                            }
                        } );
                    }
                } );

            } );

            file_frame.open();
        }

        function renderOneUploader( el ) {

            var file_frame;

            if ( undefined !== file_frame ) {
                file_frame.open();
                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media( {
                frame    : 'post',
                state    : 'insert',
                multiple : false
            } );

            file_frame.on( 'insert', function () {

                file_frame.state().get( 'selection' ).each( function ( image ) {

                    $.ajax( {
                        url     : ajaxurl,
                        data    : {
                            action : 'adsw_get_image',
                            id     : image.id,
                            size   : 'medium'
                        },
                        type    : "POST",
                        success : function ( response ) {
                            el.find( '.img-responsive' ).attr( 'src', response );
                            el.find( '.item-value-img' ).val( image.id );
                        }
                    } );
                } );

            } );

            file_frame.open();
        }

        function checkExistingId( id ) {

            var el = $( obj.gallery ).find( '.image-item' );

            if ( !el.length ) return false;

            id = id.toString();

            var res = false;
            el.each( function () {

                var value = $( this ).find( '[name="gallery[]"]' ).val();

                if ( value == id )
                    res = true;
            } );

            return res;
        }

        function getFormData() {
            return $( 'form[name="post"]' ).serialize();
        }

        return {

            manageGallery    : function () {

                var el   = obj.gallery;
                var item = '.image-item';

                $( el ).sortable();

                $( el ).on( 'click', '[data-toggle="remove"]', function () {
                    $( this ).parents( item ).remove();
                } );

                $( el ).on( 'click', '[data-toggle="move-left"]', function () {
                    var $th = $( this ).parents( item );
                    if ( $th.prev().length ) {
                        $th.prev().before( $th );
                    }
                } );

                $( el ).on( 'click', '[data-toggle="move-right"]', function () {
                    var $th = $( this ).parents( item );
                    if ( $th.next().length ) {
                        $th.next().after( $th );
                    }
                } );
            },
            manageAttributes : function () {

                var el = $( "#attributes" );

                el.find( '.attr-inner' ).sortable();

                el.on( 'click', '.attr-inner .remove', function ( e ) {
                    e.preventDefault();
                    $( this ).parents( '.attr-item-line' ).remove();
                } );

                el.on( 'click', '.add', function ( e ) {
                    e.preventDefault();
                    el.find( '.attr-inner' ).append( $( obj.row ).html() );
                } );
            },
            manageSku        : function () {

                var $el = $( obj.sku );

                $( 'a[href="#ads-tab-inventory"]' ).on( 'click', function () {
                    if ( $( this ).parent( 'li.active' ).length )return;

                    ADS.coverShow();
                    var data = getFormData();
                    $.ajaxQueue( {
                        url      : ajaxurl,
                        dataType : 'json',
                        data     : {
                            action      : 'ads_save_post_actions',
                            ads_actions : 'get_sku_attr',
                            form        : ADS.b64EncodeUnicode( data )
                        },
                        type     : "POST",
                        success  : function ( response ) {
                            $( '#ads-tab-inventory' ).html( response.html );
                            ADS.coverHide();
                        }
                    } );
                } );

                $( '#ads-tab-variation' ).on( 'click', 'a', function () {
                    var d = $( this ).data( 'toggle' );
                    if ( d == 'addRow' ) {
                        var ul = $( '#ads-sku' );

                        var k = (function () {
                            var prop = [],
                                max  = 0;

                            $( '#ads-sku li[data-prop_id]' ).each( function ( e, i ) {
                                prop.push( $( this ).data( 'prop_id' ) );
                            } );

                            if ( prop.length )
                                max = Math.max.apply( null, prop );

                            return max + 1;
                        })();

                        var ulObj = {
                            k          : k,
                            prop_title : '',
                            items      : [
                                {
                                    k          : k,
                                    key        : k + ':1',
                                    title      : '',
                                    img        : '',
                                    sku_id     : '1',
                                    prop_title : '',
                                    url        : document.location.origin + '/wp-content/plugins/alids/assets/img/empty.png',
                                }
                            ]
                        };

                        var template = Handlebars.compile($('#tmpl-sku-big' ).html());
                        Handlebars.registerPartial("items", $('#tmpl-sku-item').html());

                        ul.append( template( ulObj ) );
                        $( 'body' ).trigger( {
                            type : "sku:change"
                        } );
                    }
                } );

                $el.on( 'click', 'a', function () {

                    var d = $( this ).data( 'toggle' );

                    if ( d == 'plus' ) {
                        $( this ).data( 'toggle', 'minus' ).find( 'span' ).removeClass( 'glyphicon-plus' ).addClass( 'glyphicon-minus' );
                        $( this ).parents( '.item' ).find( '.more' ).show();
                    }
                    else if ( d == 'minus' ) {
                        $( this ).data( 'toggle', 'plus' ).find( 'span' ).removeClass( 'glyphicon-minus' ).addClass( 'glyphicon-plus' );
                        $( this ).parents( '.item' ).find( '.more' ).hide();
                    }
                    else if ( d == 'remove' ) {
                        $( this ).parents( 'li.item' ).remove();
                    }
                    else if ( d == 'removeItem' ) {

                        var th = $( this ).parents( 'li.item' );

                        if ( th.find( '.more' ).length > 1 ) {
                            $( this ).parent().remove();
                        }
                    }
                    else if ( d == 'addItem' ) {
                        var li = $( this ).closest( 'li.item' );

                        var sku_id = (function () {
                            var sku = [],
                                max = 0;
                            li.find( '[data-sku_id]' ).each( function ( e, i ) {
                                sku.push( $( this ).data( 'sku_id' ) );
                            } );

                            if ( sku.length )
                                max = Math.max.apply( null, sku );

                            return max + 1;
                        })();

                        var k = $( li ).data( 'prop_id' );

                        var obj  = {
                            k          : k,
                            key        : k + ':' + sku_id,
                            title      : '',
                            img        : '',
                            sku_id     : sku_id,
                            prop_title : '',
                            url        : document.location.origin + '/wp-content/plugins/alids/assets/img/empty.png',
                        };
                        var tmpl = $( '#tmpl-sku-item' ).html();
                        li.append( ADS.objTotmpl( tmpl, obj ) );
                    }
                    else if ( d == 'addImg' ) {
                        renderOneUploader( $( this ).parents( '.more-item' ) );
                    }
                    else if ( d == 'removeImg' ) {
                        var dimg = $( '#default-img' ).val(),
                            t    = $( this ).parent();

                        t.find( '.item-value-img' ).val( '' );
                        t.find( '.img-responsive' ).attr( 'src', dimg );
                    }
                } );
            },
            init             : function () {

                this.manageAttributes();
                this.manageGallery();
                this.manageSku();

                $('#save').on('click', function () {
                    if (!status) {
                        var data = getFormData();
                        //console.log(data);
                        $('#ads-tab-inventory [name]').removeAttr("name");
                        $.ajaxQueue({
                            url      : ajaxurl,
                            dataType : 'json',
                            data     : {
                                action : 'adsw_save_review',
                                form   : ADS.b64EncodeUnicode(data)
                            },
                            type     : "POST",
                            success  : function (response) {
                                console.log(response);
                                status = true;
                                $( '#save' ).click();
                            }
                        });
                    }

                    return status;
                });

                $( '#ads-upload-image' ).on( 'click', function ( e ) {

                    e.preventDefault();

                    renderMediaUploader();

                } );
            },
            save             : function () {
                var data = getFormData();
                $.ajaxQueue( {
                    url      : ajaxurl,
                    dataType : 'json',
                    data     : {
                        action : 'ads_save_post',
                        form   : ADS.b64EncodeUnicode( data )
                    },
                    type     : "POST",
                    success  : function ( response ) {
                    }
                } );
            }
        };
    })();

    innerPost.init();
});