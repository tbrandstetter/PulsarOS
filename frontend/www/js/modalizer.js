//Special thanks to Praveen Rajappan for his code: http://blog.praveenr.com/
var Modalizer=new Class({defaultModalStyle:{display:'block',position:'fixed',top:0,left:0,'z-index':5000,'background-color':'#333',opacity:0.8},setModalOptions:function(options){this.modalOptions=$merge({width:(window.getScrollSize().x),height:(window.getScrollSize().y),elementsToHide:'select, embed'+(Browser.Engine.trident?'':', object'),hideOnClick:true,modalStyle:{},updateOnResize:true,layerId:'modalOverlay',onModalHide:$empty,onModalShow:$empty},this.modalOptions,options);return this},layer:function(){if(!this.modalOptions.layerId)this.setModalOptions();return document.id(this.modalOptions.layerId)||new Element('div',{id:this.modalOptions.layerId}).inject(document.body)},resize:function(){if(this.layer()){this.layer().setStyles({width:(window.getScrollSize().x),height:(window.getScrollSize().y)})}},setModalStyle:function(styleObject){this.modalOptions.modalStyle=styleObject;this.modalStyle=$merge(this.defaultModalStyle,{width:this.modalOptions.width,height:this.modalOptions.height},styleObject);if(this.layer())this.layer().setStyles(this.modalStyle);return(this.modalStyle)},modalShow:function(options){this.setModalOptions(options);this.layer().setStyles(this.setModalStyle(this.modalOptions.modalStyle));if(Browser.Engine.trident4)this.layer().setStyle('position','absolute');this.layer().removeEvents('click').addEvent('click',function(){this.modalHide(this.modalOptions.hideOnClick)}.bind(this));this.bound=this.bound||{};if(!this.bound.resize&&this.modalOptions.updateOnResize){this.bound.resize=this.resize.bind(this);window.addEvent('resize',this.bound.resize)}if($type(this.modalOptions.onModalShow)=="function")this.modalOptions.onModalShow();this.togglePopThroughElements(0);this.layer().setStyle('display','block');return this},modalHide:function(override,force){if(override===false)return false;this.togglePopThroughElements(1);if($type(this.modalOptions.onModalHide)=="function")this.modalOptions.onModalHide();this.layer().setStyle('display','none');if(this.modalOptions.updateOnResize){this.bound=this.bound||{};if(!this.bound.resize)this.bound.resize=this.resize.bind(this);window.removeEvent('resize',this.bound.resize)}return this},togglePopThroughElements:function(opacity){if(Browser.Engine.trident4||(Browser.Engine.gecko&&Browser.Platform.mac)){$$(this.modalOptions.elementsToHide).each(function(sel){sel.setStyle('opacity',opacity)})}}});


//Helper class to center element in current window, show element etc
var WindowHelper = {
    defaultOptions: {
        onError: $empty, animate: true
    },
    defaultStyle: {
        position: 'fixed', 'z-index': 5100
    },
    _minOf: function(x, y) { // Utility function to find min of 2 numbers
        if (($type(x) != 'number') || ($type(y) != 'number')) {
            return -1;
        }
        if (x > y) {
            return y;
        }
        return x;
    },
    _maxOf: function(x, y) { // Utility function to find max of 2 numbers
        if (($type(x) != 'number') || ($type(y) != 'number')) {
            return -1;
        }
        if (x < y) {
            return y;
        }
        return x;
    },
    centerWindow: function(element, options) {
        if (element) {
            var top = window.getSize().y / 2;
            var left = window.getSize().x / 2;
            var opts = $merge(this.defaultOptions, options);

            element.setStyle("display", "block"); //Without this below line does not give correct value.
            top = this._maxOf(top - (element.getSize().y / 2), 0);
            left = this._maxOf(left - (element.getSize().x / 2), 0);

            element.setStyle("top", top);
            element.setStyle("left", left);
            element.setStyles(this.defaultStyle);

            if (Browser.Engine.trident4) element.setStyle('position', 'absolute');

            return true;
        }
    },
    showElement: function(element, options) {
        element.setStyle("visibility", "hidden");
        element.setStyle("opacity", 0);
        element.setStyle("display", "block");

        if (options && options.animate) {
            var myTween =
                new Fx.Morph(element,
                    {
                          duration: 1000
                        , onComplete: this._showComplete(element)
                    });
            myTween.start({ 'opacity': [0, 1] });
        } else {
            element.setStyle("opacity", 1);
        }
    },
    _showComplete: function(element) {
        element.setStyle('display', 'block');
        element.setStyle('visibility', 'visible');
    },
    _closeComplete: function(element, options) {
        element.setStyle('display', 'none');
        if ($type(options.onWindowClose) == "function") {
            options.onWindowClose();
        }
    },
    close: function(element, options) {
        if (element) {
            if (options && options.animate) {
                var myTween =
                    new Fx.Morph(element,
                        {
                              duration: 300
                            , onComplete: this._closeComplete(element, options)
                        });
                myTween.start({ 'opacity': [.5, 0] });
            } else {
                this._closeComplete(element, options);
            }
        }
    }
};

var CTModalizer = {
    modalInstance: this.modalInstance || new Modalizer(),
    defaultOpts: {
          opacity: '0.5'
        , hideOnClick: false
        , 'z-index': 5000
        , onPreGrab: $empty
        , animate: true
        , onWindowClose: $empty
        , updateOnResize: true
    },
    init: function(elementIdToGrab, options) {
        var opts = $merge(this.defaultOpts, options);

        $$('.close').each(function(el) {
            el.removeEvents("click");
        });

        $$('.close').each(function(el) {

        el.addEvent("click", function() {
                this.closeGrabbedWindow(elementIdToGrab);
            } .bind(this));
        }, this);

        var grabEl = $(elementIdToGrab);
        if (opts.updateOnResize) {
            window.addEvent("resize", this._resize(grabEl, opts));
        }
    },
    _resize: function(grabEl, opts) {
        WindowHelper.centerWindow(grabEl, opts);
    },
    grab: function(elementIdToGrab, options) {
        var grabEl = $(elementIdToGrab);
        this.init(elementIdToGrab, options);
        if (WindowHelper.centerWindow(grabEl, options)) {
            var opts = $merge(this.defaultOpts, options);
            WindowHelper.showElement(grabEl, opts);
            this.modalInstance.modalShow(opts);
            if ($type(opts.OnPreGrab) == "function") {
                opts.OnPreGrab();
            }
        }
    },
    closeGrabbedWindow: function(grabbedElementId) {
        var el = $(grabbedElementId);
        el.setStyle("z-index", "4100");
        WindowHelper.close(el, this.modalInstance.modalOptions);
        this.modalInstance.modalHide();
    }
};