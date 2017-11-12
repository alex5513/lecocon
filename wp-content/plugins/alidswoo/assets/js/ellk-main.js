/**
 * Created by Vitaly Kukin on 20.03.2016.
 */
jQuery(function($){

    window.ADS = {

        cover: '.fade-cover',
        coverAppend : function(){
            $('body').append(
                '<div class="fade-cover">' +
                '<div id="Plane">' +
                '<div id="Plane_1" class="Plane"></div>' +
                '<div id="Plane_2" class="Plane"></div>' +
                '<div id="Plane_3" class="Plane"></div>' +
                '<div id="Plane_4" class="Plane"></div>' +
                '<div id="Plane_5" class="Plane"></div>' +
                '<div id="Plane_6" class="Plane"></div>' +
                '<div id="Plane_7" class="Plane"></div>' +
                '<div id="Plane_8" class="Plane"></div>' +
                '</div>' +
                '</div>'
            );
        },
        isURL : function (str) {
            var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
            if( ! regex.test(str)) {
                return false;
            } else {
                return true;
            }
        },
        coverShow : function(){
            $(ADS.cover).show();
        },
        coverHide : function(){
            $(ADS.cover).hide();
        },
        tryJSON : function ( data ) {
            try {
                var response = $.parseJSON( data );
            }
            catch ( e ) {
                console.log(data);
                return false;
            }

            return response;
        },
        newRow : function( $obj, args ){

            var $tr = $('<tr/>');

            for( var i = 0; i < args.length; i++ ){
                var $td = $('<td/>').append(args[i]);
                $tr.append($td);
            }

            $obj.append($tr);
        },
        loader : function(a, action){

            var $o = $(a).parents('.panel-body').find('.fade-cover');
            if(action == 'show')
                $o.show();
            else
                $o.hide();
        },
        replace: function (search, replace, subject, count) {

            var i = 0,
                j = 0,
                temp = '',
                repl = '',
                fl = 0,
                f = [].concat(search),
                r = [].concat(replace),
                s = subject,
                ra = Object.prototype.toString.call(r) === '[object Array]',
                sa = Object.prototype.toString.call(s) === '[object Array]';

            s = [].concat(s);

            if (count) {
                this.window[count] = 0;
            }

            var sl = s.length;

            for (i, sl; i < sl; i++) {
                if (s[i] === '') {
                    continue;
                }
                for (j = 0, fl = f.length; j < fl; j++) {
                    temp = s[i] + '';
                    repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
                    s[i] = (temp).split(f[j]).join(repl);
                    if (count && s[i] !== temp) {
                        this.window[count] += (temp.length - s[i].length) / f[j].length;
                    }
                }
            }
            return sa ? s : s[0];
        },
        objTotmpl : function ( tmpl, data ) {
            if(typeof Handlebars === 'undefined'){
                console.log('Handlebars not registry');
                return false
            }
            var template = Handlebars.compile(tmpl);
            return template(data);
        },
        scrollToNode: function (node) {
            $(node).focus();
            var top = $(node).offset().top - 100;
            $('body,html').stop().animate({
                scrollTop: top
            }, 1000);
        },
        toasters : function(){

            var alert = $(document).find('#ads-notify');

            if( alert.length && alert.html()){
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-full-width",
                    "preventDuplicates": false,
                    "showDuration": "300",
                    "hideDuration": "3000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };

                toastr.info(alert.html());
            }
        },
        init: function () {
            this.toasters();
            this.coverAppend();
        },
        notify: function( msg ) {

            var el = $(document).find('#ads-notify');

            if( el.length )
                el.html(msg);
            else
                $(document).find('body').append(
                    $('<div>').css('display','none').attr('id', 'ads-notify').html(msg)
                );

            this.toasters();
        },
        b64EncodeUnicode : function(str) {
            return btoa(
                encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
                    return String.fromCharCode('0x' + p1);
                })
            );
        },

        Dispatcher : {
            subscribers : [],

            /**
             *
             * @param {string} event
             * @param {function} observer
             * @param {object} context
             * @param info
             * @param {boolean} one
             *
             * @example
             * Dispatcher.on('adsGoogleExtension:name', function(e){}, this, {a1:123})
             */
            on: function( event, observer, context, info, one ) {

                context = context || null;
                info = info || null;
                one = one || false;

                var handler = {
                    observer:observer,
                    context: context,
                    info: info,
                    one: one
                };

                if ( this.subscribers.hasOwnProperty( event ) ) {
                    this.subscribers[ event ].push( handler );
                } else {
                    this.subscribers[ event ] = [ handler ];
                }
            },
            one: function( event, observer, context, info ) {
                context = context || null;
                info = info || null;
                this.on( event, observer, context, info, true );
            },

            trigger: function( event, data ) {
                console.log( this.subscribers );
                for ( var ev in this.subscribers ) {
                    if ( ev !== event ) {
                        continue;
                    }
                    if ( this.subscribers.hasOwnProperty( ev ) ) {
                        console.log( ev );
                        this.subscribers[ ev ].forEach( function( handler, i ){
                            handler.observer.call( handler.context, data, handler.info );
                            if ( handler.one ) {
                                this.subscribers[ ev ].splice( i, 1 );
                            }
                        } );
                    }
                }
            }

        }
    };

    Pleasure.init();
    Layout.init();
    ADS.init();

    $('.ellk-menu-thumbler').on('click', function(e){
        e.preventDefault();

        var show = $(this).data('show'),
            hide = $(this).data('hide');
        $(this).removeClass('active').hide();
        $('.menu-'+show).addClass('active').show();

        $('#adminmenu .'+show).show();
        $('#adminmenu .'+hide).hide();
    });

    $(document).on('change keyup', 'input', function(){

        var type = ['submit', 'radio', 'checkbox'];

        if($(this).val() != '' ) $(document).find('input').each(function(){
            if( $.inArray($(this).attr('type'), type) == -1 &&
                $(this).val() != '' &&
                $(this).next().length > 0 &&
                $(this).next().prop('tagName') == 'LABEL' )
                    $(this).addClass('valid');
        });
    });

});