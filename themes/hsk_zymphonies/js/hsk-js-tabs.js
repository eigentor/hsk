(function ($) {
  Drupal.behaviors.hsk_js_tabs = {
    attach: function (context, settings) {

      /**
       * Create Tabs for the text fields in the "turnier" content type, so that each table is displayed
       * in a separate tab.
       */

      // function rufzeichen_tabs {
      //
      // }

      $('.page-node-type-turnier #info-tabs').once('tabcontainer').each(function() {
        // Add a container for the tab buttons
        $(this).before('<div id="table-tabs"></div>');

        // Clone the field labels as Tab-Button titles and add a tab button
        // for each field. Add some classes.
        $('.page-node-type-turnier #info-tabs > .field').each(function(index, element){
          $(this).find('.field__label').clone().removeClass('field__label').addClass('tab-' + index)
            .addClass('tab-button').appendTo('#table-tabs');
          $(this).addClass('tab-' + index);
        });

        // add an active class to the active tab button and the active tab content
        $('.page-node-type-turnier #table-tabs .tab-button:first-child').addClass('active');
        $('.page-node-type-turnier #info-tabs .field:first-child').addClass('info-tab-active');

        // The show and hide function for the tabs and tab buttons
        $('.page-node-type-turnier #table-tabs .tab-button').click(function(){
          var $this = $(this);
            // Get the index number of the tab that was clicked
            var tab_number = $this.index() + 1;
            $this.addClass('active');
            $this.siblings().removeClass('active');
            // Show the info-tab with the same number
          $('.page-node-type-turnier #info-tabs .field:nth-of-type(' + tab_number + ')').each(function(){
            $(this).show().addClass('info-tab-active');
            $(this).siblings().hide().removeClass('info-tab-active');
          });
        });




      })



    }
  };
})(jQuery);