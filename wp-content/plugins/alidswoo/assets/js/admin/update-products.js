jQuery( function ( $ ) {

	var update = (function () {
		var $this,
			$body   = $( 'body' ),
			storage = {
				active : false
			};

		var obj = {
			'btn_start' : '#js-updateProduct',
			'action'    : 'adsw_update_product'
		};

		var data = {
			"count"   : 0,
			"current" : 0,
			"update"  : 0,
			"product" : {},
			"info"    : {}
		};

		function setData( name, value, trigger ) {
			if ( typeof name === "string" ) {
				data[ name ] = value;
			} else {
				for ( var i in name ) {
					data[ i ] = name[ i ];
				}
				trigger = value;
			}

			if ( trigger !== true ) return;

			$body.trigger( {
				type  : "update:data",
				info  : data,
				chage : {
					name  : name,
					value : value
				}
			} );
		}

		function getInfo( cb ) {
            cb = cb || function (  ) {};
            $.ajaxQueue( {
                url     : ajaxurl,
                data    : {
                    action      : obj.action,
                    ads_actions : 'info'
                },
                type    : "POST",
                success : function ( response ) {
                    response = ADS.tryJSON( response );
                    setData( response, true );
                    cb(response);
                }
            } );
		}

		function send( post_id, data ) {
			data = data || {};
			data.action = obj.action;
			data.ads_actions = 'apply';
			data.post_id = post_id;
			$.ajaxQueue( {
				url     : ajaxurl,
				data    : data,
				type    : "POST",
				success : function ( response ) {
					response = ADS.tryJSON( response );
                    setData( response, true );
					upload();
				}
			} );
		}

		function getNextProduct() {
			$.ajaxQueue( {
				url      : ajaxurl,
				data     : {
					action      : obj.action,
					ads_actions : 'next'
				},
				type     : "POST",
				success  : function ( response ) {
                    response     = ADS.tryJSON( response );
                    data.current = response.current;
					if ( data.current != -1 ) {

						data.product[ response.post_id ] = {
							post_id : response.post_id,
							product_id : response.product_id,
							url : response.url
						};
						if(response.url !== false){
							window.ADS.aliExpansion.addTask( response.url, sendUpdate, $this, response.post_id );
						} else {
							console.log('url empty:', response);
							delete (data.list);
							setData( response, true );
							upload();
						}

					} else {
						data.product = {};
						delete (data.list);
						setData( response, true );
						$( obj.btn_start ).removeClass( 'disabled' );
					}

				},
				complete : function () {

				}
			} );
		}

		function sendUpdate( e ) {

			var $obj    = e.obj,
				post_id = e.index,
				url     = data.product[ post_id ][ 'url' ],
				product = window.ADS.aliParseProduct.parseObj( $obj, url );

			send(post_id, {
				product     : ADS.b64EncodeUnicode( JSON.stringify( product ) ),
				setting     : {status: $('#status').val(), cost:$('#cost').val(), stock:$('#stock').val() }
			});
		}

		function upload() {
			if ( storage.active && data.current != -1 ) {
				getNextProduct();
			}
		}

		return {
			init : function () {

				var $this = this;
				$body.on( 'click', obj.btn_start, function () {

                    if ( storage.active ) {
						storage.active = false;
                        data = {
                            "count"   : 0,
                            "current" : 0,
                            "update"  : 0,
                            "product" : {},
                            "info"    : {}
                        };
                        $body.trigger('clear:data');
                        getInfo();
                        setTimeout(function(){
                            $(obj.btn_start).click();
						}, 500);
					} else {
						storage.active = true;
						upload();
					}
				} );

				getInfo();
			}
		}
	})();
	update.init();

	/**
	 * title +count
	 * @type {{init}}
	 */
	var updateInfo;
	updateInfo = (function () {
		var $this;
		var $body = $( 'body' );

		var data = {
			list : []
		};

		var obl  = {
			$list           : $( '#ads-activities-list' ),
			$updateProgress : $( '#js-update-progress' )
		};
		var tmpl = {
			list : $( '#tmpl-activities-list' ).html()
		};


		return {
			init           : function () {
				$this = this;

				ChartsKnob.init();

				$( 'body' ).on( 'clear:data', function(){
                    data = { list : [] };
                    obl.$list.html('');
                    obl.$updateProgress.val(0).trigger('change');
				} );

				$( 'body' ).on( 'update:data', function ( e ) {

                    if ( e.info.hasOwnProperty( 'list' ) ) {

						$this.setList( e.info.list );
						$this.renderList();
					}

					$this.renderProgress( e.info );
				} );
			},
			renderProgress : function ( info ) {
				var c  = info.current,
					i  = parseInt( info.count );
				var pr = c==-1 ? 100 : parseInt( c / i * 100 );
				obl.$updateProgress.val( pr ).trigger( 'change' );
			},
			renderList     : function () {
				data.list.reverse();
                obl.$list.html( ADS.objTotmpl( tmpl.list, data ) );
				data.list.reverse();
			},
			setList        : function ( list ) {

				if( list.title != '' )
					data.list.push( {
						title   : list.title,
						count   : list.count > 0 ? '+' + list.count : 0,
						img     : list.img,
						caption : list.caption
					} );

				if ( data.list.length > 10 )
					data.list.splice( 0, 1 );

			}
		}
	})();

	updateInfo.init();
} );