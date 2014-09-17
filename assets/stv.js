(function( $, undefined ) {
	"use strict";

    var _defaults = {
        'expandIcon': 'fa-expand-o',
        'collapseIcon': 'fa-collapse-o',
    };
    var _dataKey = 'simpleTreeView';
    var _searchTimeout;

    //case insensitive :contains selector
    jQuery.expr[':'].containsCI = function(a, i, m) {
      return jQuery(a).text().toLowerCase()
          .indexOf(m[3].toLowerCase()) >= 0;
    };

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

        if (_settings.searchInput) {
            var prevSearchText = '';
            $(_settings.searchInput).on('keyup', function(e) {
                var searchText = $.trim($(this).val());
                if (prevSearchText !== searchText) {
                    prevSearchText = searchText;
                    clearTimeout(_searchTimeout);
                    _searchTimeout = setTimeout(function(){
                        _methods.searchTree.apply( _element, [searchText] );
                    }, 200);
                }
            });
        }

        return _settings;
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
        destroy: function() {
            $.removeData(this, _dataKey);
            return this;
        },
        toggleBranch: function(event) {
            var settings = $(this).parents('ul.stv-list:last').data(_dataKey);
            var item = $(event.target).closest('li');
            $(item).children('ul').toggle();
            $(event.target)
                .toggleClass(settings.collapseIcon)
                .toggleClass(settings.expandIcon);
            event.stopPropagation();
        },
        toggleAll: function(show) {
            var settings = $(this).data(_dataKey);
            $('ul', this).toggle(show);
            $('.toggle', this)
                .toggleClass(settings.expandIcon, !show)
                .toggleClass(settings.collapseIcon, show);
            return this;
        },
        collapseAll: function() {
            return _methods.toggleAll.apply(this, [false]);
        },
        expandAll: function() {
            return _methods.toggleAll.apply(this, [true]);
        },
        searchTree: function(searchText) {
            var settings = $(this).data(_dataKey);
            $(this).simpleTreeView('collapseAll');
            if (searchText === '') {
                $('li', this).show();
            } else {
                $('li', this).hide();
                $('ul', this).hide();
                $(':containsCI(\''+searchText+'\')', this).parents('li').show().each(function(){
                    $('.toggle', $(this).children('.stv-item'))
                        .toggleClass(settings.expandIcon, false)
                        .toggleClass(settings.collapseIcon, true);
                });
                $(':containsCI(\''+searchText+'\')', this).parents('ul').show();
            }
        }
    };
}( jQuery ));



