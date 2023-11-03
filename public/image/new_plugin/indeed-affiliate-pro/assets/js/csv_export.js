/*
 *Ultimate Affiliate Pro - CSV Export
 */

"use strict";

var UapCsvExport = {

    triggerSelector             : '',

    init: function( args ){
        var obj = this;
        obj.setAttributes( obj, args );
        window.addEventListener( 'DOMContentLoaded', function(){
            jQuery( obj.triggerSelector ).on( 'click', function( evt ){
                obj.handleExport( obj, evt );
            });
        });
    },

    setAttributes: function( obj, args ){
        for (var key in args) {
          obj[key] = args[key];
        }
    },

    handleExport: function( obj, evt ){
        jQuery.ajax({
            type      : "post",
            url       : decodeURI(ajax_url),
            data      : {
                 action			  : 'uap_ajax_make_csv',
                 exportType   : jQuery( evt.target ).attr( 'data-export_type' ),
                 filters      : jQuery( evt.target ).attr( 'data-filters' ),
            },
            success   : function ( response ) {
                if ( response == 0 ){
                    return false;
                }
                jQuery( evt.target ).parent().after( '<a href="' + response + '">Download CSV</a>' );
                window.open( response, '_blank' );
            }
        });
    }
};

UapCsvExport.init({
    triggerSelector             : '.js-uap-export-csv',
});
