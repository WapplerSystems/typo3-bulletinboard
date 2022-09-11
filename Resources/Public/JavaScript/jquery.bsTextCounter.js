
+function ($) {

    'use strict';

    var BsTextCounter = function (element, options) {
        this.options    = options;
        this.$element   = $(element);
        this.$status    = $(this.options.containerElement);

        this.options.limit = parseInt(this.$element.data('maximum-chars'));

        // Add classes to the status container, and insert base text
        this.$status
            .addClass(this.options.containerClass + ' ' + this.options.counterClass)
            .append('' +
                this.options.limit + '/'+this.options.limit);

        // reference not available til we've appended the html snippet
        this.$count     = $(this.$status);

        this.$element.after(this.$status);

        $(this.$status).wrap('<div></div>');

        // set our event handler and proxy it to properly set the context
        this.$element.on('input.BsTextCounter.data-api propertychange.BsTextCounter.data-api', $.proxy(this.checkCount, this));

        // and run initial check of current value
        this.checkCount();
    };

    BsTextCounter.VERSION = '0.0.1';
    BsTextCounter.NAME = 'BsTextCounter';

    BsTextCounter.DEFAULTS = {
        selector: '.form-control-bstextcounter',
        limit: 200,
        warningLimit: 10,
        // These two are Bootstrap text emphasis classes
        // that you can override in the config, or roll
        // your own of the same name
        counterClass: 'label-primary',
        warningClass: 'label-danger',
        containerElement: '<span>',
        containerClass: 'bstextcounter label'
    };

    BsTextCounter.prototype.checkCount = function () {
        var currVal = this.$element.val();

        if (currVal.length > this.options.limit) {
            // reset the currVal, so that it stays within the limit
            currVal = currVal.substr(0, this.options.limit - 1);
            this.$element.val(currVal);
        }

        var remaining = this.options.limit - currVal.length;

        this.$count.html(remaining + '/'+this.options.limit);

        if (remaining <= this.options.warningLimit) {
            this.$status.removeClass(this.options.counterClass).addClass(this.options.warningClass);
        } else {
            this.$status.removeClass(this.options.warningClass).addClass(this.options.counterClass);
        }
    };

    BsTextCounter.prototype.destroy = function () {
        $.removeData(this.$element[0], 'BsTextCounter');

        // remove the inserted status container
        if (!this.options.statusMessage.length) {
            this.$status.remove();
        } else {
            this.$status
                .removeClass(
                    this.options.containerClass + ' ' +
                    this.options.counterClass + ' ' +
                    this.options.warningClass)
                .empty();
        }

        this.$element.off('input.BsTextCounter.data-api propertychange.BsTextCounter.data-api');
        this.$element = null;
    };

    // BsTextCounter Plugin Definition

    function Plugin (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('BsTextCounter'),
                options = $.extend({}, BsTextCounter.DEFAULTS, $this.data(), typeof option == 'object' && option);

            if (!data) $this.data('BsTextCounter', (data = new BsTextCounter(this, options)));
            if (typeof option == 'string') data[option]();
        });
    }

    var old = $.fn.BsTextCounter;

    $.fn.BsTextCounter              = Plugin;
    $.fn.BsTextCounter.Constructor  = BsTextCounter;

    // BsTextCounter No Conflict

    $.fn.BsTextCounter.noConflict = function () {
        $.fn.BsTextCounter = old;
        return this;
    };



}(jQuery);

$(document).ready(function() {

  $('.form-control-bstextcounter').BsTextCounter();

});
