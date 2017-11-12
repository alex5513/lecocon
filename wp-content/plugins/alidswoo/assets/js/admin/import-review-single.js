jQuery(function($) {
    var Review = new (function() {
        var self = this;
        var translate = '+N+';
        var withPictures = false;
        var ignoreImages = false;
        var approved     = false;
        var countReviews = 10;
        var importedReview = 0;
        var onlyFromMyCountry = false;
        var page = 1;
        var feedbackUrl = $('#importUrl').val();
        var post_id = $('#post_id').val();
        var productUrl = '';
        var obj = {
            'btn_start' : '#js-reviewImport',
            'action'    : 'adsw_actions_review'
        };

        var feedList = [];

        this.resetAttributes = function() {
            $('.adsw_update_attributes').on('click', function(e){
                e.preventDefault();

                productUrl = $(this).data('product');

                if( productUrl != '' ) {

                    $("#woocommerce-product-data").block({
                        message: null,
                        overlayCSS: {
                            background: "#fff",
                            opacity: .6
                        }
                    });

                    setTimeout(function() {
                        window.ADS.aliExpansion.addTask(productUrl, self.sendAttributes, self);
                    }, 200);
                }
                else{
                    self.reviewNotify($('#noUrlMessage').val());
                    return false;
                }
            });
        };

        this.sendAttributes = function(e){

            var $obj = e.obj;
            var product = window.ADS.aliParseProduct.parseObj( $obj, productUrl );
            var data = {};

            data.action      = 'adsw_update_product';
            data.ads_actions = 'reset_attributes';
            data.product     = ADS.b64EncodeUnicode(JSON.stringify(product.params));
            data.post_id     = post_id;

            $.ajaxQueue({
                url     : ajaxurl,
                data    : data,
                type    : "POST",
                success : function (response) {

                    $("#woocommerce-product-data").unblock();//остановить анимацию

                    response = ADS.tryJSON(response);
                    if (response !== false) {

                        if(typeof response.error != 'undefined') {
                            self.reviewNotify(response.error);
                        } else {
                            self.reviewNotify(response.success);
                            setTimeout(function(){
                                window.location.reload();
                            }, 1000);
                        }
                    }
                }
            });
        };

        self.singleImport = $('#js-reviewSingleImport');

        this.importReviewButton = function() {
            self.singleImport.on('click', function(e) {
                e.preventDefault();
                translate = $("input[name='translate']").is(':checked') ? '+Y+' : '+N+';
                countReviews = $("select[name='count_review']").val();
                ignoreImages = $("input[name='ignoreImages']").is(':checked');
                approved = $("input[name='approved']").is(':checked');
                withPictures = $("input[name='withImage']").is(':checked');
                onlyFromMyCountry = $("input[name='onlyFromMyCountry']").is(':checked');
                if (ignoreImages) {
                    withPictures = false;
                }
                self.showProgress();
                self.addReview(post_id);
            });

            $("input[name='ignoreImages']").on('change', function(e) {
                var hideElements = ['withImage'];
                if ($(this).is(':checked')) {
                    ignoreImages = true;
                    $.each(hideElements, function(index, value) {
                        var containerDiv = $('#' + value).parents('.switcher');
                        containerDiv.hide();
                        containerDiv.next().hide();
                        $("input[name='" + value + "']").prop('checked', false);
                    });
                } else {
                    ignoreImages = false;
                    $.each(hideElements, function(index, value) {
                        var containerDiv = $('#' + value).parents('.switcher');
                        containerDiv.show();
                        containerDiv.next().show();
                    });
                }
            });
        };

        this.addReview = function(postId) {
            url = self.changeUrl(
                feedbackUrl,
                {
                    'translate'         : translate,
                    'page'              : page,
                    'withPictures'      : withPictures,
                    'onlyFromMyCountry' : onlyFromMyCountry
                }
            );

            if (!url) {
                self.hideProgress();
                self.reviewNotify($('#noReviewMessage').val());
                return false;
            }

            setTimeout(function() {
                window.ADS.aliExpansion.addTask(url, self.sendReview, self, postId);
            }, 2000);
        };

        this.showProgress = function() {
            $.each($('.fade-cover'), function (index, value) {
                if ($(value).attr('id') != 'review_animate') {
                    $(value).remove();
                }
            });
            ADS.coverShow();
            importedReview = 0;
            page = 1;
        };

        this.hideProgress = function() {
            ADS.coverHide();
        };

        this.sendReview = function(e) {
            var $obj = e.obj;

            var feedListLength = self.fillFeedList($obj);

            if (feedListLength > 0 && importedReview <= countReviews) {
                page++;
                self.addReview(post_id);
                self.send(
                    post_id,
                    {
                        feed_list    : ADS.b64EncodeUnicode(JSON.stringify(feedList)),
                        star_min     : $( '#min-star' ).val(),
                        withPictures : withPictures,
                        ignoreImages : ignoreImages,
                        approved     : approved
                    }
                );
            } else {
                self.finishImport();
            }
        };

        this.send = function (post_id, data) {
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
                    if (response !== false) {
                        importedReview = importedReview + response.list.count;
                        feedList = [];
                    }
                }
            });
        };

        this.getStar = function(width) {
            var star;
            width = parseInt(width.replace(/[^0-9]/g, ''));

            star = 0;
            if (width > 0) {
                star = parseInt( 5 * width / 100 );
            }

            return star;
        };

        this.fillFeedList = function($obj) {
            $feedbackList = $obj.find('.feedback-list-wrap .feedback-item');
            $feedbackList.each(function(i, e) {
                review          = {};
                images          = [];
                review.feedback = $(this).find('.buyer-feedback').text();
                review.feedback = review.feedback.replace('seller', 'store');
                review.flag     = $(this).find('.css_flag').text();
                review.author   = $(this).find('.user-name').text();
                review.star     = self.getStar($(this).find('.star-view span').attr('style'));

                $(this).find('.pic-view-item').each(function(index, value) {
                    images.push($(value).data('src'));
                });

                review.date = $(this).find('.r-time').text();
                review.images = images;
                feedList.push(review);
            });
            console.log(feedList);
            return $feedbackList.length;
        };

        this.parseUrl = function(url) {
            var chipsUrl = url.split('?');
            var hostName = chipsUrl[0];
            var paramsUrl = chipsUrl[1];
            if (typeof paramsUrl == 'undefined') {
                return false;
            }
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
        };

        this.buildUrl = function(hostName, urlArray) {
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
        };

        this.changeUrl = function(url, params) {
            if (typeof params == 'undefined') {
                return false;
            }

            var result = self.parseUrl(url);
            if (!result) {
                return false;
            }

            $.each(params, function(key, value) {
                result.urlArray[key] = value;
            });

            return self.buildUrl(result.hostName, result.urlArray);
        };

        this.reviewNotify = function(message) {
            ADS.notify(message) ;
        };

        this.finishImport = function() {
            self.hideProgress();
            var message = $('#countReviewMessage').val() + ' ' + importedReview;
            self.reviewNotify(message);
        };

        this.init = function() {
            self.importReviewButton();
            self.resetAttributes();
        }
    })();

    Review.init();
});