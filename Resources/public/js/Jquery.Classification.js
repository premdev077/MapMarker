/**
 * dataChain
 * Version 1.0
 * Description: jQuery plugin to handle chain selects
 * Requires jQuery ~2.1.1, jquery.livequery
 * Author: Premraj Tharmarajah
 * Copyright: Copyright 2016 Absolute Value Limited
 * License: MIT
 */

// Plugin closure wraper
;(function ($, document, window, undefined) {

    "use strict"

    var DataChain = function (element, options) {
        this.options = options;
        this.$element = $(element);
        this.options.target - typeof this.options.target === 'object' ? this.options.target : $(this.options.target);

        // Store the "parent" which is the select that limits the choices of "this" select
        this.$parent = $(this.options.chain);

        // Store the element value
        this.val = this.$element.val();

        // Also, let's store the immediate child, i.e. the select, if any, that depends on this one
        // Get the select that depends on this one
        this.$child = $('select[data-chain="#' + this.$element.attr('id') + '"]');
    }
    // We use the extend functionality to avoid prototype conflicts
    $.extend(DataChain.prototype, {

        constructor: DataChain,

        init: function () {

            // Load the appropriate values.
            this.load();

            // Set up "that" to sidestep closure issue, i.e. which this?
            var that = this;    

            // Bind parent
            this.$parent.on("change", function () {
                // Clear the child
                that.clear();
                // load the children
                that.load();
                
            });

        },

        clear: function () {
            var child = this.$child.data('dataChain');
            if (typeof child === 'object') {
                child.clear();
            }

            if (this.options.emptyValues) {
                this.$element.find("option:gt(0)").remove();
            } else {
                this.$element.find("option").remove();
            }
        },

        load: function () {
            // Use jQuery to load a JSON array of objects representing the available values for this element given the
            // value of the parent element.

            // Now clear so that we're dealing with a fresh select
            this.clear();

            // inside the AJAX closure, we use "that", as "this" will no longer refer to the DataChain instance
            var that = this;

            $.ajax({
                type: 'POST',
                url: that.options.url,
                data: {
                    parent: that.$parent.val()
                },
                success: function (response) {
                    $(response).each(function (index, obj) {
                        var option;

                        if (obj.id == that.val) {
                            option = '<option value="' + obj.id + '" selected="selected">' + obj.description + '</option>';
                        } else {
                            option = '<option value="' + obj.id + '">' + obj.description + '</option>';
                        }

                        that.$element.append(option);
                    });

                    that.$element.trigger("change");
                    /*
                                                                        
                    */
                }
            });
        }
    });

    $.fn.dataChain = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('dataChain'),
                options = $.extend({}, $.fn.dataChain.defaults, $this.data(), typeof option == 'object' && option);

            if (!data) $this.data('dataChain', (data = new DataChain(this, options)))

            data.init();
        });
         
        // Maintain jQuery chaining
        return this;
    },
    
    $.fn.dataChain.defaults = {
        url: '/categories',
        emptyValues: true
    },

    
    $(document).ready(function () {
        $("select[data-chain]").livequery(function () {
            $(this).dataChain();
        });
    });

})(jQuery, document)