(function ($) {
  Drupal.behaviors.hsk_js_tabs = {
    attach: function (context, settings) {

      /**
       * A Tabs script. Why did I write this myself: It works with any set of sibling elements.
       * No need for ul/li Structure, no need for seperate anchor (a) elements.
       * We only need one selector: the sibling elements that become tabs in the end.
       * The entire rest is cloned and created from them.
       */

      function rufzeichen_tabs(siblings_to_tab_content) {
        $(siblings_to_tab_content).once('tabcontainer').each(function() {
          // Add a container for the tab buttons
          $(this).before('<div id="table-tabs"></div>');

          // set a variable for the main selector so we can use it comfortably also
          // inside elements where $(this) would not apply.
          // It also makes the code more readable since we know it is the main selector.
          $selector = $(this);

          // Clone the field labels as Tab-Button titles and add a tab button
          // for each field. Add some classes.
          $selector.children('.field').each(function(index, element){
            $(this).find('.field__label').clone().removeClass('field__label').addClass('tab-' + index)
              .addClass('tab-button').appendTo('#table-tabs');
            $(this).addClass('tab-' + index);
          });

          // add an active class to the active tab button and the active tab content
          $('#table-tabs .tab-button:first-child').addClass('active');
          $selector.children('.field:first').addClass('info-tab-active');

          // The show and hide function for the tabs and tab buttons
          $('#table-tabs .tab-button').click(function(){
            var $this = $(this);
            // Get the index number of the tab that was clicked
            var tab_number = $this.index() + 1;
            $this.addClass('active');
            $this.siblings().removeClass('active');
            // Show the info-tab with the same number
            $selector.children('.field:nth-of-type(' + tab_number + ')').each(function(){
              $(this).show().addClass('info-tab-active');
              $(this).siblings().hide().removeClass('info-tab-active');
            });
          });

        });
      }


      // Execute the rufzeichen_tabs function with the desired selector
      rufzeichen_tabs('.page-node-type-turnier #info-tabs');



    }
  };
})(jQuery);