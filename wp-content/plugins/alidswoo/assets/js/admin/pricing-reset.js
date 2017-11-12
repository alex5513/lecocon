/**
 * Created by pavel on 15.07.2016.
 */

jQuery( function ( $ ) {

	var update = (function () {
		var $this,
			$body   = $( 'body' ),
			storage = {
				active : false
			};

		var obj = {
			'btn_start' : '#js-resetPrice',
			'action'    : 'adsw_reset_pricing'
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

		function getInfo() {
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
				}
			} );
		}

		function send( post_id ) {

			$.ajaxQueue( {
				url     : ajaxurl,
				data    : {
					action      : obj.action,
					ads_actions : 'apply',
					post_id     : post_id
				},
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
							post_id : response.post_id
						};
						send( response.post_id );

					} else {
						data.product = {};
						$( obj.btn_start ).removeClass( 'disabled' );
					}
				},
				complete : function () {}
			} );
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
			$list           : $( '#ads-activities-list-resetPrice' ),
			$updateProgress : $( '#js-update-progress-resetPrice' )
		};
		var tmpl = {
			list : $( '#tmpl-activities-list' ).html()
		};


		return {
			init           : function () {
				$this = this;

				ChartsKnob.init();

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
				var pr = parseInt( c / i * 100 );
				obl.$updateProgress.val( pr ).trigger( 'change' );
			},
			renderList     : function () {
				data.list.reverse();
				obl.$list.html( ADS.objTotmpl( tmpl.list, data ) );
				data.list.reverse();
			},
			setList        : function ( list ) {

				data.list.push( {
					title   : list.title,
					count   : list.count > 0 ? '+' + list.count : 0,
					img     : list.img,
					caption : list.caption,
					link    : list.link
				} );

				if ( data.list.length > 10 )
					data.list.splice( 0, 1 );

			}
		}
	})();

	updateInfo.init();
} );