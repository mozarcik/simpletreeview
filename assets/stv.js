(function( $, undefined ) {
	"use strict";

    var _defaults = {};
    var _dataKey = 'simpleTreeView';
    
    $.fn.simpleTreeView = function(method) {
        if ( _methods[method] ) {
			return _methods[ method ].apply( this, arguments);
		} else if ( typeof method === 'object' || ! method ) {
			return _methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.eDataTables' );
		}               
    };


    function SimpleTreeView(element, settings) {
        var _settings = settings;
        var _element = element;

        $('.stv-item > i.toggle', _element).click(_methods.toggleBranch);
    };

    var _methods = {
        init: function(settings) {
            settings = $.extend(_defaults, settings || {});

            this.each(function(i, _element) {
                var element = $(_element);
                var stv = new SimpleTreeView(element, settings);
                element.data(_dataKey, stv);
            });
            return this;
        },
        destroy: function() {$.removeData(this, _dataKey); return this;},
        toggleBranch: function(event) {

            var item = $(event.target).closest('li');
            $(item).children('ul').toggle();
            $(event.target).toggleClass('fa-collapse-o').toggleClass('fa-expand-o');
            event.stopPropagation();
        },
        toggleAll: function(show) {
            console.info('toggle');
            $('ul', this).toggle(show);
            $('.toggle', this)
                .toggleClass('fa-expand-o', !show)
                .toggleClass('fa-collapse-o', show);
            return this;
        },
        collapseAll: function() { return _methods.toggleAll.apply(this, [false]); },
        expandAll: function() { return _methods.toggleAll.apply(this, [true]); }
    };
}( jQuery ));



