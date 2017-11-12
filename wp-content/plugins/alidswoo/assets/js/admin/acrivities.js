/**
 * Created by Vitaly on 31.05.2016.
 */
jQuery(function($){
    var ListOrders = (function (){

        var $this;
        var $body = $("body");

        var $_GET = {};

        document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {

            function decode(s) {
                return decodeURIComponent(s.split("+").join(" "));
            }

            $_GET[decode(arguments[1])] = decode(arguments[2]);
        });

        function dateRangePicker(a){

            if( typeof $(a) == 'undefined' ) return false;

            $(a).find('span').html(
                moment().subtract(29,'days').format('MMMM D, YYYY')+' - '+ moment().format('MMMM D, YYYY')
            );
            $(a).daterangepicker({
                startDate:moment().subtract(29,'days')
            });
            console.log($_GET);

            $(a).on('apply.daterangepicker',function(ev, picker){
                $(this).find('span').html(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY'));
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));

                var from = picker.startDate.format('YYYY-MM-DD'),
                    to = picker.endDate.format('YYYY-MM-DD');

                $('#date-from').val(from);
                $('#date-to').val(to);

                var link = $('input[name="uri"]').val()+'&date-from='+ from +'&date-to='+ to;

                if( typeof $_GET.status != "undefined" )
                    link += '&status='+$_GET.status;

                window.location.replace(link);
            });
        }

        return {

            createPagination : function (total, current, perPage) {

                var $obj = $('.pagination-menu');

                $obj.pagination({
                    items: parseInt(total),
                    itemsOnPage: parseInt(perPage),
                    currentPage: parseInt(current),
                    hrefTextPrefix: '#page-',
                    cssStyle: "light-theme",
                    prevText: $obj.data('prev'),
                    nextText: $obj.data('next'),

                    onPageClick: function (pageNumber){
                        var link = $('input[name="link"]').val()+'&ads-page='+pageNumber;
                        window.location.replace(link);
                    }
                });
            },
            checker : function(){

                var a = $("#checkAll"),
                    l = $('#orders_wrapper').find("tbody");
                a.change(function () {
                    l.find('input:checkbox').prop('checked', $(this).prop("checked"));
                });

                l.on('click', 'input:checkbox', function(){
                    var u = l.find("input:checkbox:not(:checked)");

                    if( u.length && a.prop( "checked" ) ){
                        a.prop( "checked", false );
                    }
                    else if( u.length == 0 && ! a.prop( "checked" )){
                        a.prop( "checked", true );
                    }
                });
            },
            init: function () {

                $this = this;

                dateRangePicker('.bootstrap-daterangepicker-dropdown');

                $this.checker();

                if( $('.pagination-menu').length ){
                    var total = $('input[name="total"]').val(),
                        current = $('input[name="current"]').val(),
                        perPage = $('input[name="perPage"]').val();

                    this.createPagination(total, current, perPage);
                }

                $('button[name="bulk-sent"]').on('click', function(e){

                    var l = $('#orders_wrapper').find("tbody"),
                        items = l.find('input:checkbox:checked'),
                        v = [],
                        th = $(this).parents('form');

                    if( items.length == 0 || th.find('select[name="bulk-action"]').val() == 'none' )
                        e.preventDefault();

                    items.each(function(){
                        v.push( $(this).val() );
                    });

                    th.find('input[name="move-items"]').val(v.join());
                });
            }
        }
    })();

    ListOrders.init();
});