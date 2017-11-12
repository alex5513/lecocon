/**
 * Created by sunfun on 16.06.2016.
 */


jQuery( function ( $ ) {

	var test_connection = (function () {
		var $this;
		var $body = $('body');
		var obj = {
			$modal : $( '#panel-modal-test-connection' )
		};

		function testApi() {
			$.ajaxQueue( {
				url      : ajaxurl,
				data     : {
					action     : 'ads_actions_test_api',
					trackingId : $( '#trackingId' ).val(),
					AppKey     : $( '#AppKey' ).val()
				},
				type     : "POST",
				success  : function ( response ) {
					obj.$modal.modal( 'show' );
					ADS.coverHide();
					obj.$modal.find('.modal-body .api' ).html(response);
				},
				complete : function () {

				}
			} );
		}

		return {
			init    : function () {
				$( '#test_connection' ).on( 'click', function ( e ) {
					e.preventDefault();
					obj.$modal.modal( 'show' );
					//ADS.coverShow();
					/*testApi();*/

				} );

				$body.on('test:chrome',function(e){
					if( e.active){
						obj.$modal.find('.browser.alert-success' ).show();//.html();
					}else{
						obj.$modal.find('.browser.alert-danger' ).show();//.html();
					}
				});

				$body.on('test:extensions',function(e){
					if( e.active){
						obj.$modal.find('.browser-ex.alert-success' ).show();//.html();
					}else{
						obj.$modal.find('.browser-ex.alert-danger' ).show();//.html();
					}
				});
			}

		}

	})();
	test_connection.init();

} );
