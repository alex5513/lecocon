/**
 * Created by pavel on 18.04.2016.
 */

jQuery( function ( $ ) {

	var registerHelpersHandlebars = {
		init : function () {
			Handlebars.registerHelper( 'selected', function ( option, value ) {
				if ( option === value ) {
					return ' selected';
				} else {
					return ''
				}
			} );
		}
	};

	registerHelpersHandlebars.init();

	var tableEditor = (function () {
		var $this, $body;
		var obj  = {
			main            : '#edit-formula',
			rows            : '#edit-rows',
			form            : '#js-edit-form',
			btnNew          : '#dt-new',
			btnEdit         : '.dt-edit',
			btnDelete       : '.dt-delete',
			btnSave         : '#dt-save, .dt-save',
			btnCancel       : '#dt-cancel, .dt-cancel',
			btnNewSelection : '.dt-new-selection',
			template        : {
				rowEdit : $( '#tmpl-row-edit' ).html(),
				row     : $( '#tmpl-row' ).html()
			}
		};
		var sign = {
			plus     : '+',
			multiply : '*',
			none     : '='
		};
		var data = [];

		var rowDefault = {
			min         : '',
			max         : '',
			sign        : '',
			formula     : ''
		};
		/**/
		function makeString( object ) {
			if ( object == null ) return '';
			return String( object );
		}

		function escapeRegExp( str ) {
			return makeString( str ).replace( /([.*+?^=!:${}()|[\]\/\\])/g, '\\$1' );
		}

		function defaultToWhiteSpace( characters ) {
			if ( characters == null )
				return '\\s';
			else if ( characters.source )
				return characters.source;
			else
				return '[' + escapeRegExp( characters ) + ']';
		}

		function trim( str, characters ) {
			var nativeTrim = String.prototype.trim;
			str            = makeString( str );
			if ( !characters && nativeTrim ) return nativeTrim.call( str );
			characters = defaultToWhiteSpace( characters );
			return str.replace( new RegExp( '^' + characters + '+|' + characters + '+$', 'g' ), '' );
		}

		function clone( obj ) {
			return $.extend( {}, obj );
		}

		function viewFilterRow( row ) {
			row[ 'minRow' ]     = row[ 'min' ] && row[ 'min' ] > 0 ? '$' + row[ 'min' ] : 0;
			row[ 'maxRow' ]     = row[ 'max' ] ? '$' + row[ 'max' ] : '∞';
			row[ 'signSymbol' ] = sign.hasOwnProperty( row[ 'sign' ] ) ? sign[ row[ 'sign' ] ] : '';

			return row;
		}

		function rowEdit( id ) {
			var row = clone( data[ id ] );

			row[ 'id' ]      = id;
			row[ 'signAll' ] = sign;

			row = viewFilterRow( row );

			var edirRow = ADS.objTotmpl( obj.template.rowEdit, row );

			if(0 == id){
				$('#edit-rows').append('<div class="row-formula" data-id="0"></div>');
			}

			$( '.row-formula[data-id="' + id + '"]' ).replaceWith( edirRow );
			$( 'select.selecter', obj.form ).selectpicker();
		}

		function rowDelete( id ) {
			delete data[ id ];
		}

		function rowSave( id, rowData ) {
			var i,
				row = clone( rowDefault );

			for ( i in rowData ) {
				if ( 'formula' == rowData[ i ][ 'name' ] ) {

					row[ 'formula' ] = rowData[ i ][ 'value' ].replace( /,/g, "." ).replace( /[^\d\.]+/g, "" ).trim();
					row[ 'formula' ] = row[ 'formula' ] ? row[ 'formula' ] : 0;

				} else if ( 'min' == rowData[ i ][ 'name' ] ) {

					row[ 'min' ] = rowData[ i ][ 'value' ].replace( /,/g, "." ).replace( /[^\d\.]+/g, "" ).trim();

				} else if ( 'max' == rowData[ i ][ 'name' ] ) {

					row[ 'max' ] = rowData[ i ][ 'value' ].replace( /,/g, "." ).replace( /[^\d\.]+/g, "" ).trim()

				} else if ( 'sign' == rowData[ i ][ 'name' ] ) {
					row[ 'sign' ] = rowData[ i ][ 'value' ]
				}
			}

			row[ 'min' ] = parseFloat( row[ 'min' ] ) > parseFloat( row[ 'max' ] ) ? 0 : row[ 'min' ];

			data[ id ] = row;

			$body.trigger( {
				type : "table-formula:save",
				rows : data
			} );
		}

		function render() {
			var i, num = 1;
			var $rows  = $( obj.rows );

			$rows.html( '' );

			for ( i in data ) {
				if(i !=0){
					var row      = clone( data[ i ] );
					row[ 'id' ]  = i;
					row[ 'num' ] = num++;

					row = viewFilterRow( row );

					$rows.append( ADS.objTotmpl( obj.template.row, row ) );
				}

			}
		}

		function getData() {

			$.ajaxQueue( {
				url      : ajaxurl,
				dataType : 'json',
				data     : {
					action : 'adsw_options_pricing_markup_formula' //todo
				},
				type     : "POST",
				success  : function ( response ) {
					data = response;

					if( typeof data != 'object')
						console.log(typeof data);

					$body.trigger( {
						type : "table-formula:load",
						rows : data
					} );
				}
			} );


			$body.trigger( {
				type : "table-formula:load",
				rows : data
			} );
		}

		function send() {
			$.ajaxQueue( {
				url     : ajaxurl,
				data    : {
					action : 'adsw_save_pricing_markup_formula', //todo
					rows   : data
				},
				type    : "POST",
				success : function ( response ) {
					if ( response == 'true' ) {
						ADS.notify( $( '#successPricing' ).html() );
					} else {
						ADS.notify( $( '#errorPricing' ).html(), 'error' )
					}
				}
			} );
		}

		return {
			init   : function () {
				$this = this;
				$body = $( 'body' );
				$this.events();
				getData();
			},
			events : function () {
				$body.on( 'table-formula:load', function () {
					render();
				} );
				$body.on( 'table-formula:save', function () {
					send();
					getData();
				} );

				$body.on( 'click', obj.btnEdit, function ( e ) {
					e.preventDefault();
					var id = $( this ).closest( 'tr' ).data( 'id' );
					render();
					rowEdit( id );
				} );
				$body.on( 'click', obj.btnDelete, function ( e ) {
					e.preventDefault();
					var id = $( this ).closest( 'tr' ).data( 'id' );
					rowDelete( id );
					send();
					render();
					getData();
				} );
				$body.on( 'click', obj.btnSave, function ( e ) {
					e.preventDefault();
					var id      = $( this ).closest( 'tr' ).data( 'id' );
					var rowData = $( obj.form ).serializeArray();
					rowSave( id, rowData );

				} );
				$body.on( 'click', obj.btnCancel, function ( e ) {
					e.preventDefault();
					render();
				} );
				$body.on( 'click', obj.btnNew, function ( e ) {
					e.preventDefault();
					render();
					rowEdit( 0 );
				} );

				$body.on( 'click', obj.btnNewSelection, function ( e ) {
					e.preventDefault();
					var selection = $( this ).data( 'selection' );

					data[ 0 ] = clone( rowDefault );
					data[ 0 ][ 'formula' ] = selection;
					data[ 0 ][ 'sign' ]    = 'multiply';
					render();
					rowEdit( 0 );
				} );

			}

		}
	})();
	tableEditor.init();
} );

jQuery( function ( $ ) {

	var update = (function () {
		var $this,
			$body   = $( 'body' ),
			storage = {
				active : false
			};

		var obj = {
			'btn_start' : '#js-updatePrice'
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
					action      : 'adsw_update_pricingMarkupFormula',
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
					action      : 'adsw_update_pricingMarkupFormula',
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
					action      : 'adsw_update_pricingMarkupFormula',
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
				complete : function () {

				}
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
					if(storage.active){
						storage.active = false;
					}else{
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
			$list : $( '#ads-activities-list' )
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
				$( '#js-update-progress' ).val( pr ).trigger( 'change' );
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