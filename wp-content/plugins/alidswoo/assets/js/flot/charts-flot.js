jQuery(function($){

    function weekendAreas(axes) {

        var markings = [],
            d = new Date(axes.xaxis.min);

        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);

        var i = d.getTime();

        do {
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        } while (i < axes.xaxis.max);

        return markings;
    }

	window.ChartsFlot = {

		orders: function (a) {

            if( $(a).length == 0 ) return false;

            var data_chart = [[{"label":"Undefined"}],[{"data": []},{"Total":"0"}]],
                d_chart = $(a).parent().find('.chart_data').text();

            if( d_chart != "" )
                data_chart = $.parseJSON( d_chart );

            var d1 = [];
            $.each(data_chart.data, function(d, v){
                d1.push([d, v]);
            });

            var atick = "#eee",
                acol  = "#e02222";

            if( typeof $(a).data('tick') != 'undefined' )
                atick = $(a).data('tick');

            if( typeof $(a).data('colors') != 'undefined' )
                acol = $(a).data('colors');

            var data 		= [{data: d1,label: data_chart.label}],
                options 	= {
                    series: {
                        lines: {show: true,lineWidth:3,fill: true,fillColor: {colors: [{opacity: 0.05}, {opacity: 0.01}]}},
                        points: {show: true},shadowSize: 2
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        tickColor: atick,
                        borderWidth: 0,
                        markings: weekendAreas
                    },
                    colors: [acol],
                    xaxis: {mode: "time", tickLength: 5},
                    yaxis: {ticks: 5,tickDecimals: 0}
                };

            var plot = $.plot(a, data, options);
            this.tooltip(plot, a);
		},
        showTotal : function( a, t ){

            var th = $(a).parent();

            if( th.find('.chart-total span').length > 0 )
                th.find('.chart-total span').html(t);
            else
                th.append("<div class='chart-total-chart'><div class='chart-total'><span>"+t+"</span></div></div>");
        },
        chartReloader : function(a){

            if( $(a).length == 0 ) return false;

            var data_chart = [[{"label":"Undefined"}],[{"data": []},{"total":"Total: 0"}]],
                d_chart    = $(a).parent().find('.chart_data').text();

            if( d_chart != "" )
                data_chart = $.parseJSON( d_chart );

            var d1 = [];

            $.each(data_chart.data, function(d, v){
                d1.push([d, v]);
            });

            var atick = "#eee",
                acol  = "#e02222";

            if( typeof $(a).data('tick') != 'undefined' )
                atick = $(a).data('tick');

            if( typeof $(a).data('colors') != 'undefined' )
                acol = $(a).data('colors');

            var data 		= [{data: d1,label: data_chart.label}],
                options 	= {
                    series: {
                        lines: {show: true,lineWidth:3,fill: true,fillColor: {colors: [{opacity: 0.05}, {opacity: 0.01}]}},
                        points: {show: true},shadowSize: 2
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        tickColor: atick,
                        borderWidth: 0,
                        markings: weekendAreas
                    },
                    colors: [acol],
                    xaxis: {mode: "time", tickLength: 5},
                    yaxis: {ticks: 5,tickDecimals: 0}
                };
            var plot = $.plot(a, data, options);
            this.tooltip(plot, a);
            this.showTotal(a, data_chart.total);
        },
        tooltip: function(plot, a){

            $('<div id="tooltip"></div>').css({
                position: "absolute",
                display: "none",
                border: "1px solid #333",
                padding: "4px",
                color: '#fff',
                'border-radius': '3px',
                'background-color': '#333',
                opacity: 0.80
            }).appendTo("body");

            var t = $('#tooltip');

            $(a).on("plothover", function (event, pos, item) {

                if (item) {
                    var y = item.datapoint[1].toFixed(0),
                        d = new Date( item.datapoint[0]),
                        locale = "en-us",
                        month = d.toLocaleString(locale, { month: 'short' }),

                        x = d.getDate()+" "+month;

                    t.html(y + ' - ' + x)
                        .css({top: item.pageY-15, left: item.pageX+8})
                        .fadeIn(200);
                } else {
                    t.hide();
                }
            });
        },

		init: function(){}
	};
});