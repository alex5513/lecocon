
!function(e){e(["jquery"],function(e){return function(){function t(e,t,n){return f({type:O.error,iconClass:g().iconClasses.error,message:e,optionsOverride:n,title:t})}function n(t,n){return t||(t=g()),v=e("#"+t.containerId),v.length?v:(n&&(v=c(t)),v)}function i(e,t,n){return f({type:O.info,iconClass:g().iconClasses.info,message:e,optionsOverride:n,title:t})}function o(e){w=e}function s(e,t,n){return f({type:O.success,iconClass:g().iconClasses.success,message:e,optionsOverride:n,title:t})}function a(e,t,n){return f({type:O.warning,iconClass:g().iconClasses.warning,message:e,optionsOverride:n,title:t})}function r(e){var t=g();v||n(t),l(e,t)||u(t)}function d(t){var i=g();return v||n(i),t&&0===e(":focus",t).length?void h(t):void(v.children().length&&v.remove())}function u(t){for(var n=v.children(),i=n.length-1;i>=0;i--)l(e(n[i]),t)}function l(t,n){return t&&0===e(":focus",t).length?(t[n.hideMethod]({duration:n.hideDuration,easing:n.hideEasing,complete:function(){h(t)}}),!0):!1}function c(t){return v=e("<div/>").attr("id",t.containerId).addClass(t.positionClass).attr("aria-live","polite").attr("role","alert"),v.appendTo(e(t.target)),v}function p(){return{tapToDismiss:!0,toastClass:"toast",containerId:"toast-container",debug:!1,showMethod:"fadeIn",showDuration:300,showEasing:"swing",onShown:void 0,hideMethod:"fadeOut",hideDuration:1e3,hideEasing:"swing",onHidden:void 0,extendedTimeOut:1e3,iconClasses:{error:"toast-error",info:"toast-info",success:"toast-success",warning:"toast-warning"},iconClass:"toast-info",positionClass:"toast-top-right",timeOut:5e3,titleClass:"toast-title",messageClass:"toast-message",target:"body",closeHtml:'<button type="button">&times;</button>',newestOnTop:!0,preventDuplicates:!1,progressBar:!1}}function m(e){w&&w(e)}function f(t){function i(t){return!e(":focus",l).length||t?(clearTimeout(O.intervalId),l[r.hideMethod]({duration:r.hideDuration,easing:r.hideEasing,complete:function(){h(l),r.onHidden&&"hidden"!==b.state&&r.onHidden(),b.state="hidden",b.endTime=new Date,m(b)}})):void 0}function o(){(r.timeOut>0||r.extendedTimeOut>0)&&(u=setTimeout(i,r.extendedTimeOut),O.maxHideTime=parseFloat(r.extendedTimeOut),O.hideEta=(new Date).getTime()+O.maxHideTime)}function s(){clearTimeout(u),O.hideEta=0,l.stop(!0,!0)[r.showMethod]({duration:r.showDuration,easing:r.showEasing})}function a(){var e=(O.hideEta-(new Date).getTime())/O.maxHideTime*100;f.width(e+"%")}var r=g(),d=t.iconClass||r.iconClass;if("undefined"!=typeof t.optionsOverride&&(r=e.extend(r,t.optionsOverride),d=t.optionsOverride.iconClass||d),r.preventDuplicates){if(t.message===C)return;C=t.message}T++,v=n(r,!0);var u=null,l=e("<div/>"),c=e("<div/>"),p=e("<div/>"),f=e("<div/>"),w=e(r.closeHtml),O={intervalId:null,hideEta:null,maxHideTime:null},b={toastId:T,state:"visible",startTime:new Date,options:r,map:t};return t.iconClass&&l.addClass(r.toastClass).addClass(d),t.title&&(c.append(t.title).addClass(r.titleClass),l.append(c)),t.message&&(p.append(t.message).addClass(r.messageClass),l.append(p)),r.closeButton&&(w.addClass("toast-close-button").attr("role","button"),l.prepend(w)),r.progressBar&&(f.addClass("toast-progress"),l.prepend(f)),l.hide(),r.newestOnTop?v.prepend(l):v.append(l),l[r.showMethod]({duration:r.showDuration,easing:r.showEasing,complete:r.onShown}),r.timeOut>0&&(u=setTimeout(i,r.timeOut),O.maxHideTime=parseFloat(r.timeOut),O.hideEta=(new Date).getTime()+O.maxHideTime,r.progressBar&&(O.intervalId=setInterval(a,10))),l.hover(s,o),!r.onclick&&r.tapToDismiss&&l.click(i),r.closeButton&&w&&w.click(function(e){e.stopPropagation?e.stopPropagation():void 0!==e.cancelBubble&&e.cancelBubble!==!0&&(e.cancelBubble=!0),i(!0)}),r.onclick&&l.click(function(){r.onclick(),i()}),m(b),r.debug&&console&&console.log(b),l}function g(){return e.extend({},p(),b.options)}function h(e){v||(v=n()),e.is(":visible")||(e.remove(),e=null,0===v.children().length&&(v.remove(),C=void 0))}var v,w,C,T=0,O={error:"error",info:"info",success:"success",warning:"warning"},b={clear:r,remove:d,error:t,getContainer:n,info:i,options:{},subscribe:o,success:s,version:"2.1.0",warning:a};return b}()})}("function"==typeof define&&define.amd?define:function(e,t){"undefined"!=typeof module&&module.exports?module.exports=t(require("jquery")):window.toastr=t(window.jQuery)});

(function( factory ) {
    if (typeof define !== 'undefined' && define.amd) {
        define([], factory);
    } else if (typeof module !== 'undefined' && module.exports) {
        module.exports = factory();
    } else {
        window.scrollMonitor = factory();
    }
})(function() {

    var scrollTop = function() {
        return window.pageYOffset ||
            (document.documentElement && document.documentElement.scrollTop) ||
            document.body.scrollTop;
    };

    var exports = {};

    var watchers = [];

    var VISIBILITYCHANGE = 'visibilityChange';
    var ENTERVIEWPORT = 'enterViewport';
    var FULLYENTERVIEWPORT = 'fullyEnterViewport';
    var EXITVIEWPORT = 'exitViewport';
    var PARTIALLYEXITVIEWPORT = 'partiallyExitViewport';
    var LOCATIONCHANGE = 'locationChange';
    var STATECHANGE = 'stateChange';

    var eventTypes = [
        VISIBILITYCHANGE,
        ENTERVIEWPORT,
        FULLYENTERVIEWPORT,
        EXITVIEWPORT,
        PARTIALLYEXITVIEWPORT,
        LOCATIONCHANGE,
        STATECHANGE
    ];

    var defaultOffsets = {top: 0, bottom: 0};

    var getViewportHeight = function() {
        return window.innerHeight || document.documentElement.clientHeight;
    };

    var getDocumentHeight = function() {
        // jQuery approach
        // whichever is greatest
        return Math.max(
            document.body.scrollHeight, document.documentElement.scrollHeight,
            document.body.offsetHeight, document.documentElement.offsetHeight,
            document.documentElement.clientHeight
        );
    };

    exports.viewportTop = null;
    exports.viewportBottom = null;
    exports.documentHeight = null;
    exports.viewportHeight = getViewportHeight();

    var previousDocumentHeight;
    var latestEvent;

    var calculateViewportI;
    function calculateViewport() {
        exports.viewportTop = scrollTop();
        exports.viewportBottom = exports.viewportTop + exports.viewportHeight;
        exports.documentHeight = getDocumentHeight();
        if (exports.documentHeight !== previousDocumentHeight) {
            calculateViewportI = watchers.length;
            while( calculateViewportI-- ) {
                watchers[calculateViewportI].recalculateLocation();
            }
            previousDocumentHeight = exports.documentHeight;
        }
    }

    function recalculateWatchLocationsAndTrigger() {
        exports.viewportHeight = getViewportHeight();
        calculateViewport();
        updateAndTriggerWatchers();
    }

    var recalculateAndTriggerTimer;
    function debouncedRecalcuateAndTrigger() {
        clearTimeout(recalculateAndTriggerTimer);
        recalculateAndTriggerTimer = setTimeout( recalculateWatchLocationsAndTrigger, 100 );
    }

    var updateAndTriggerWatchersI;
    function updateAndTriggerWatchers() {
        // update all watchers then trigger the events so one can rely on another being up to date.
        updateAndTriggerWatchersI = watchers.length;
        while( updateAndTriggerWatchersI-- ) {
            watchers[updateAndTriggerWatchersI].update();
        }

        updateAndTriggerWatchersI = watchers.length;
        while( updateAndTriggerWatchersI-- ) {
            watchers[updateAndTriggerWatchersI].triggerCallbacks();
        }

    }

    function ElementWatcher( watchItem, offsets ) {
        var self = this;

        this.watchItem = watchItem;

        if (!offsets) {
            this.offsets = defaultOffsets;
        } else if (offsets === +offsets) {
            this.offsets = {top: offsets, bottom: offsets};
        } else {
            this.offsets = {
                top: offsets.top || defaultOffsets.top,
                bottom: offsets.bottom || defaultOffsets.bottom
            };
        }

        this.callbacks = {}; // {callback: function, isOne: true }

        for (var i = 0, j = eventTypes.length; i < j; i++) {
            self.callbacks[eventTypes[i]] = [];
        }

        this.locked = false;

        var wasInViewport;
        var wasFullyInViewport;
        var wasAboveViewport;
        var wasBelowViewport;

        var listenerToTriggerListI;
        var listener;
        function triggerCallbackArray( listeners ) {
            if (listeners.length === 0) {
                return;
            }
            listenerToTriggerListI = listeners.length;
            while( listenerToTriggerListI-- ) {
                listener = listeners[listenerToTriggerListI];
                listener.callback.call( self, latestEvent );
                if (listener.isOne) {
                    listeners.splice(listenerToTriggerListI, 1);
                }
            }
        }
        this.triggerCallbacks = function triggerCallbacks() {

            if (this.isInViewport && !wasInViewport) {
                triggerCallbackArray( this.callbacks[ENTERVIEWPORT] );
            }
            if (this.isFullyInViewport && !wasFullyInViewport) {
                triggerCallbackArray( this.callbacks[FULLYENTERVIEWPORT] );
            }


            if (this.isAboveViewport !== wasAboveViewport &&
                this.isBelowViewport !== wasBelowViewport) {

                triggerCallbackArray( this.callbacks[VISIBILITYCHANGE] );

                // if you skip completely past this element
                if (!wasFullyInViewport && !this.isFullyInViewport) {
                    triggerCallbackArray( this.callbacks[FULLYENTERVIEWPORT] );
                    triggerCallbackArray( this.callbacks[PARTIALLYEXITVIEWPORT] );
                }
                if (!wasInViewport && !this.isInViewport) {
                    triggerCallbackArray( this.callbacks[ENTERVIEWPORT] );
                    triggerCallbackArray( this.callbacks[EXITVIEWPORT] );
                }
            }

            if (!this.isFullyInViewport && wasFullyInViewport) {
                triggerCallbackArray( this.callbacks[PARTIALLYEXITVIEWPORT] );
            }
            if (!this.isInViewport && wasInViewport) {
                triggerCallbackArray( this.callbacks[EXITVIEWPORT] );
            }
            if (this.isInViewport !== wasInViewport) {
                triggerCallbackArray( this.callbacks[VISIBILITYCHANGE] );
            }
            switch( true ) {
                case wasInViewport !== this.isInViewport:
                case wasFullyInViewport !== this.isFullyInViewport:
                case wasAboveViewport !== this.isAboveViewport:
                case wasBelowViewport !== this.isBelowViewport:
                    triggerCallbackArray( this.callbacks[STATECHANGE] );
            }

            wasInViewport = this.isInViewport;
            wasFullyInViewport = this.isFullyInViewport;
            wasAboveViewport = this.isAboveViewport;
            wasBelowViewport = this.isBelowViewport;

        };

        this.recalculateLocation = function() {
            if (this.locked) {
                return;
            }
            var previousTop = this.top;
            var previousBottom = this.bottom;
            if (this.watchItem.nodeName) { // a dom element
                var cachedDisplay = this.watchItem.style.display;
                if (cachedDisplay === 'none') {
                    this.watchItem.style.display = '';
                }

                var boundingRect = this.watchItem.getBoundingClientRect();
                this.top = boundingRect.top + exports.viewportTop;
                this.bottom = boundingRect.bottom + exports.viewportTop;

                if (cachedDisplay === 'none') {
                    this.watchItem.style.display = cachedDisplay;
                }

            } else if (this.watchItem === +this.watchItem) { // number
                if (this.watchItem > 0) {
                    this.top = this.bottom = this.watchItem;
                } else {
                    this.top = this.bottom = exports.documentHeight - this.watchItem;
                }

            } else { // an object with a top and bottom property
                this.top = this.watchItem.top;
                this.bottom = this.watchItem.bottom;
            }

            this.top -= this.offsets.top;
            this.bottom += this.offsets.bottom;
            this.height = this.bottom - this.top;

            if ( (previousTop !== undefined || previousBottom !== undefined) && (this.top !== previousTop || this.bottom !== previousBottom) ) {
                triggerCallbackArray( this.callbacks[LOCATIONCHANGE] );
            }
        };

        this.recalculateLocation();
        this.update();

        wasInViewport = this.isInViewport;
        wasFullyInViewport = this.isFullyInViewport;
        wasAboveViewport = this.isAboveViewport;
        wasBelowViewport = this.isBelowViewport;
    }

    ElementWatcher.prototype = {
        on: function( event, callback, isOne ) {

            // trigger the event if it applies to the element right now.
            switch( true ) {
                case event === VISIBILITYCHANGE && !this.isInViewport && this.isAboveViewport:
                case event === ENTERVIEWPORT && this.isInViewport:
                case event === FULLYENTERVIEWPORT && this.isFullyInViewport:
                case event === EXITVIEWPORT && this.isAboveViewport && !this.isInViewport:
                case event === PARTIALLYEXITVIEWPORT && this.isAboveViewport:
                    callback.call( this, latestEvent );
                    if (isOne) {
                        return;
                    }
            }

            if (this.callbacks[event]) {
                this.callbacks[event].push({callback: callback, isOne: isOne||false});
            } else {
                throw new Error('Tried to add a scroll monitor listener of type '+event+'. Your options are: '+eventTypes.join(', '));
            }
        },
        off: function( event, callback ) {
            if (this.callbacks[event]) {
                for (var i = 0, item; item = this.callbacks[event][i]; i++) {
                    if (item.callback === callback) {
                        this.callbacks[event].splice(i, 1);
                        break;
                    }
                }
            } else {
                throw new Error('Tried to remove a scroll monitor listener of type '+event+'. Your options are: '+eventTypes.join(', '));
            }
        },
        one: function( event, callback ) {
            this.on( event, callback, true);
        },
        recalculateSize: function() {
            this.height = this.watchItem.offsetHeight + this.offsets.top + this.offsets.bottom;
            this.bottom = this.top + this.height;
        },
        update: function() {
            this.isAboveViewport = this.top < exports.viewportTop;
            this.isBelowViewport = this.bottom > exports.viewportBottom;

            this.isInViewport = (this.top <= exports.viewportBottom && this.bottom >= exports.viewportTop);
            this.isFullyInViewport = (this.top >= exports.viewportTop && this.bottom <= exports.viewportBottom) ||
                (this.isAboveViewport && this.isBelowViewport);

        },
        destroy: function() {
            var index = watchers.indexOf(this),
                self  = this;
            watchers.splice(index, 1);
            for (var i = 0, j = eventTypes.length; i < j; i++) {
                self.callbacks[eventTypes[i]].length = 0;
            }
        },
        // prevent recalculating the element location
        lock: function() {
            this.locked = true;
        },
        unlock: function() {
            this.locked = false;
        }
    };

    var eventHandlerFactory = function (type) {
        return function( callback, isOne ) {
            this.on.call(this, type, callback, isOne);
        };
    };

    for (var i = 0, j = eventTypes.length; i < j; i++) {
        var type =  eventTypes[i];
        ElementWatcher.prototype[type] = eventHandlerFactory(type);
    }

    try {
        calculateViewport();
    } catch (e) {
        try {
            window.jQuery(calculateViewport);
        } catch (e) {
            throw new Error('If you must put scrollMonitor in the <head>, you must use jQuery.');
        }
    }

    function scrollMonitorListener(event) {
        latestEvent = event;
        calculateViewport();
        updateAndTriggerWatchers();
    }

    if (window.addEventListener) {
        window.addEventListener('scroll', scrollMonitorListener);
        window.addEventListener('resize', debouncedRecalcuateAndTrigger);
    } else {
        // Old IE support
        window.attachEvent('onscroll', scrollMonitorListener);
        window.attachEvent('onresize', debouncedRecalcuateAndTrigger);
    }

    exports.beget = exports.create = function( element, offsets ) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        } else if (element && element.length > 0) {
            element = element[0];
        }

        var watcher = new ElementWatcher( element, offsets );
        watchers.push(watcher);
        watcher.update();
        return watcher;
    };

    exports.update = function() {
        latestEvent = null;
        calculateViewport();
        updateAndTriggerWatchers();
    };
    exports.recalculateLocations = function() {
        exports.documentHeight = 0;
        exports.update();
    };

    return exports;
});


!function(t,e){function i(e){this.element=e,this.$element=t(e),this.init()}var n="textareaAutoSize",h="plugin_"+n,s=function(t){return t.replace(/\s/g,"").length>0};i.prototype={init:function(){var i=(this.$element.outerHeight(),parseInt(this.$element.css("paddingBottom"))+parseInt(this.$element.css("paddingTop")));s(this.element.value)&&this.$element.height(this.element.scrollHeight-i),this.$element.on("input keyup",function(){var n=t(e),h=n.scrollTop();t(this).height(0).height(this.scrollHeight-i),n.scrollTop(h)})}},t.fn[n]=function(e){return this.each(function(){t.data(this,h)||t.data(this,h,new i(this,e))}),this}}(jQuery,window,document);