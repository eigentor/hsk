;(function( $ ) {
  Drupal.behaviors.responsiveTablesFilter = {
    attach: function (context, settings) {
      // DOM-ready auto-init of plugins.
      // Many plugins bind to an "enhance" event to init themselves on dom ready, or when new markup is inserted into the DOM
      $( function(){
        /*! Tablesaw - v3.0.0-beta.1 - 2016-09-19
         * https://github.com/filamentgroup/tablesaw
         * Copyright (c) 2016 Filament Group; Licensed MIT
        */
        $( document ).trigger( "enhance.tablesaw" );
      });
    }
  }
})( shoestring || jQuery );
