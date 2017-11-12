jQuery(function($){

	window.ChartsKnob = {

		drawKnob: function () {
			$('.knob').knob({
				width: '150',
				draw : function () {

					// checking data-fgColor attribute is hex color, if it is not convert to defined colors
					if(!/(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(this.$.attr('data-fgColor')) ) {
						var findColor = String(this.$.attr('data-fgColor'));
						this.o.fgColor = Pleasure.colors[findColor];
						this.$.css('color', Pleasure.colors[findColor] );
					}

					// "tron" case
					if(this.$.data('skin') == 'tron') {

						var a = this.angle(this.cv)  // Angle
							, sa = this.startAngle // Previous start angle
							, sat = this.startAngle // Start angle
							, ea // Previous end angle
							, eat = sat + a  // End angle
							, r = true;

						this.g.lineWidth = this.lineWidth;

						this.o.cursor
							&& (sat = eat - 0.3)
							&& (eat = eat + 0.3);

						if (this.o.displayPrevious) {
							ea = this.startAngle + this.angle(this.value);
							this.o.cursor
									&& (sa = ea - 0.3)
									&& (ea = ea + 0.3);
							this.g.beginPath();
							this.g.strokeStyle = this.previousColor;
							this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
							this.g.stroke();
						}

						this.g.beginPath();
						this.g.strokeStyle = r ? this.o.fgColor : this.fgColor ;
						this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
						this.g.stroke();

						this.g.lineWidth = 2;
						this.g.beginPath();
						this.g.strokeStyle = this.o.fgColor;
						this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
						this.g.stroke();

						return false;
					}
				}
			});
		},

		init: function () {
			this.drawKnob();
		}
	};
});