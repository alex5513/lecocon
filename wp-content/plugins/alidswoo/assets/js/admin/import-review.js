/**
 * Created by pavel on 15.07.2016.
 */

jQuery(function($) {
    var translate = '+N+';
    var withPictures = false;
    var ignoreImages = false;
    var approved = false;
    var countReviews = 10;
    var onlyFromMyCountry = false;
    var page = 1;
    var feedbackUrl;
    var feedList = [];

    var update = (function() {
        var $this,
            $body   = $('body'),
            importedReview = 0;

        var storage = {
            active : false
        };

        var obj = {
            'btn_start' : '#js-reviewImport',
            'action'    : 'adsw_actions_review'
        };

        var data = {
            "count"   : 0,
            "current" : 0,
            "update"  : 0,
            "product" : {},
            "info"    : {}
        };

        function setData(name, value, trigger) {
            if (typeof name === "string") {
                data[name] = value;
            } else {
                for (var i in name) {
                    data[i] = name[i];
                }
                trigger = value;
            }

            if (trigger !== true) {
                return;
            }

            $body.trigger({
                type  : "update:data",
                info  : data,
                chage : {
                    name  : name,
                    value : value
                }
            });
        }

        function parseUrl(url) {
            var chipsUrl = url.split('?');
            var hostName = chipsUrl[0];
            var paramsUrl = chipsUrl[1];
            var chipsParamsUrl = paramsUrl.split('&');
            var urlArray = {};

            $.each(chipsParamsUrl, function(i, value) {
                tempChips = value.split('=');
                urlArray[tempChips[0]] = tempChips[1];
            });

            return {
                'hostName' : hostName,
                'urlArray' : urlArray
            };
        }

        function buildUrl(hostName, urlArray) {
            var url = hostName + '?';
            var urlParams = [];

            $.each(urlArray, function(index, value) {
                if (typeof value == 'undefined') {
                    value = '';
                }
                urlParams.push(index + '=' + value);
            });

            url += urlParams.join('&');
            return url;
        }

        function changeUrl(url, params) {
            if (typeof params == 'undefined') {
                return false;
            }

            var result = parseUrl(url);

            $.each(params, function(key, value) {
                result.urlArray[key] = value;
            });

            return buildUrl(result.hostName, result.urlArray);
        }

        function addReview(postId) {
            url = changeUrl(
                feedbackUrl,
                {
                    'translate'         : translate,
                    'page'              : page,
                    'withPictures'      : withPictures,
                    'onlyFromMyCountry' : onlyFromMyCountry
                }
            );

            window.ADS.aliExpansion.addTask(url, sendReview, $this, postId);
        }

        function getInfo(cb) {
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

        function send(post_id, data) {
            data = data || {};
            data.action = obj.action;
            data.ads_actions = 'apply';
            data.post_id = post_id;
            $.ajaxQueue({
                url     : ajaxurl,
                data    : data,
                type    : "POST",
                success : function (response) {
                    response = ADS.tryJSON(response);
                    if (response) {
                        setData(response, true);
                        importedReview = importedReview + response.list.count;
                    } else {
                        feedList = [];
                    }
                }
            });
        }

        function getNextProduct() {
            $.ajaxQueue({
                url      : ajaxurl,
                data     : {
                    action      : obj.action,
                    ads_actions : 'next'
                },
                type     : "POST",
                success  : function ( response ) {
                    response     = ADS.tryJSON( response );
                    data.current = response.current;

                    if (data.current != -1) {
                        page = 1;
                        importedReview = 0;
                        feedList = [];
                        feedbackUrl = response.url;
                        if (response.url === false) {
                            upload();
                        } else {
                            addReview(response.post_id);
                        }
                    } else {
                        delete(data.list);
                        data.product = {};
                        $(obj.btn_start).removeClass('disabled');
                        ADS.coverHide();
                    }

                    setData(response, true);
                },
                complete : function () {

                }
            });
	}

        function sendReview(e) {
            var $obj   = e.obj, post_id = e.index;

            var review = {
                'flag'     : '',
                'author'   : '',
                'star'     : '',
                'feedback' : '',
                'date'     : ''
            };

            $feedbackList = $obj.find( '.feedback-list-wrap .feedback-item' );

            $feedbackList.each( function ( i, e ) {
                review          = {};
                images          = []; 
                review.feedback = $(this).find('.buyer-feedback').text();
                review.flag     = $(this).find('.css_flag').text();
                review.author   = $(this).find('.user-name').text();
                review.star     = getStar($(this).find('.star-view span').attr('style'));

                $(this).find('.pic-view-item').each(function(index, value) {
                    images.push($(value).data('src'));
                });

                review.date = $(this).find('.r-time').text();
                review.images = images;
                feedList.push(review);
            });

            if ($feedbackList.length != 0 && importedReview <= countReviews) {
                page++;
                addReview(post_id);
                send(post_id, {
                    feed_list     : ADS.b64EncodeUnicode(JSON.stringify(feedList)),
                    star_min      : $( '#min-star' ).val(),
                    withPictures  : withPictures,
                    ignoreImages  : ignoreImages,
                    approved      : approved
                });
            } else {
                upload();
            }
        }

        function getStar(width) {
            var star;
            width = parseInt( width.replace( /[^0-9]/g, '' ) );

            star = 0;
            if (width > 0) {
                star = parseInt( 5 * width / 100 );
            }

            return star;
        }

        function upload() {
            if (storage.active && data.current != -1) {
                getNextProduct();
            }
        }

        return {
            init : function () {

                var $this = this;
                $body.on( 'click', obj.btn_start, function () {
                    $.each($('.fade-cover'), function (index, value) {
                        if ($(value).attr('id') != 'review_animate') {
                            $(value).remove();
                        }
                    });

                    translate = $("input[name='translate']").is(':checked') ? '+Y+' : '+N+';
                    countReviews = $("select[name='count_review']").val();
                    withPictures = $("input[name='withImage']").is(':checked');
                    onlyFromMyCountry = $("input[name='onlyFromMyCountry']").is(':checked');
                    ignoreImages = $("input[name='ignoreImages']").is(':checked');
                    approved = $("input[name='approved']").is(':checked');
                    if (ignoreImages) {
                        withPictures = false;
                    }

                    if(data.current == -1){
                        ADS.coverShow();
                        setData( {
                            "count"   : 0,
                            "current" : 0,
                            "update"  : 0,
                            "product" : {},
                            "info"    : {}
                        }, true );
                        getInfo(function(){
                            upload();
                        });
                        return;
                    }

                    if (storage.active) {
                        storage.active = false;
                        ADS.coverHide();
                    } else {
                        storage.active = true;
                        ADS.coverShow();
                        upload();
                    }
                });

                $("input[name='ignoreImages']").on('change', function(e) {
                    var hideElements = ['withImage'];
                    if ($(this).is(':checked')) {
                        ignoreImages = true;
                        $.each(hideElements, function(index, value) {
                            $('#' + value).parents('.col-md-offset-25').hide();
                            $("input[name='" + value + "']").prop('checked', false);
                        });
                    } else {
                        ignoreImages = false;
                        $.each(hideElements, function(index, value) {
                            $('#' + value).parents('.col-md-offset-25').show();
                        });
                    }
                });
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
        var $body = $('body');
        var currentPostId;
        var data = {
            list: []
        };

        var obl = {
            $list: $('#ads-activities-list'),
            $updateProgress: $('#js-update-progress')
        };

        var tmpl = {
            list: $('#tmpl-activities-list').html()
        };

        return {
            init: function () {
                $this = this;

                ChartsKnob.init();

                $('body').on('update:data', function (e) {
                    currentPostId = e.info.ID;
                    $this.setList(e.info.list);
                    $this.renderList();
                    $this.renderProgress(e.info);
                });
            },
            renderProgress: function (info) {
                var c = info.current,
                    i = parseInt(info.count);
                var pr = (c == -1) ? 100 : parseInt(c / i * 100);
                obl.$updateProgress.val(pr).trigger('change');
            },
            get_count : function (collection) {
                var totalCount = 0;
                for (var index = 0; index < collection.length; index++) {
                    if (index in collection) {
                        totalCount++;
                    }
                }
                return totalCount;
            },
            delete_first : function( obj ){

                for (var i in obj) {
                    if (obj.hasOwnProperty(i) && typeof(i) !== 'function') {
                        delete obj[i];
                        break;
                    }
                }
            },
            renderList: function () {
                data.list.reverse();
                obl.$list.html(ADS.objTotmpl(tmpl.list, data));
                data.list.reverse();
            },
            setList: function (list) {

                if( typeof list != 'undefined' ) {
                    if ( typeof data.list[currentPostId] == 'undefined' ) {

                        if( this.get_count(data.list) >= 10 ) {

                            this.delete_first(data.list);
                        }

                        data.list[currentPostId] = {
                            title: list.title,
                            count: !isNaN(list.count) ? list.count : 0,
                            img: list.img,
                            caption: list.caption,
                            link: list.link
                        };
                    } else {
                        data.list[currentPostId]['count'] += !isNaN(list.count) ? list.count : 0;
                    }
                }
            }
        }
    })();
    updateInfo.init();
});