(function ($) {
  Drupal.behaviors.hsk_js_tabs = {
    attach: function (context, settings) {

      /**
       * Create Tabs for the text fields in the "turnier" content type, so that each table is displayed
       * in a separate tab.
       */

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

        $('.page-node-type-turnier #table-tabs .tab-button:first-child').addClass('active');


      })



    }
  };
})(jQuery);