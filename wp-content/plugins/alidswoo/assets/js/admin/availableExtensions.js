/**
 * Created by pavel on 29.06.2016.
 */

jQuery( function ( $ ) {

var AvailableExtensions = (function () {
	var $this,$body = $('body');
	var is_chrome = false,
		chrome_version = false;
	if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
		is_chrome = true;
		chrome_version = navigator.userAgent.replace(/^.*Chrome\/([\d\.]+).*$/i, '$1')
	}

	return {

		init: function () {
			$this = this;

			$body.trigger({
				type: "test:chrome",
				active: is_chrome
			});

			var tim = 0;
			var interval = setInterval(function () {
				tim++;

				if ($this.is()) {
					$body.trigger({
						type: "test:extensions",
						active: true
					});
					clearInterval(interval);
				} else {
					$body.trigger({
						type: "test:extensions:start"
					});
				}

				if (tim > 10 && !$this.is()) {
					clearInterval(interval);
					$body.trigger({
						type: "test:extensions",
						active: false
					});
				}
			}, 200);

		},
		is: function () {
			if($('body').hasClass('expansion-alids-init')) {
				return true;
			}
			return false;
		}
	}

})();
	$(window ).load(function(){
		AvailableExtensions.init();
	});
} );


