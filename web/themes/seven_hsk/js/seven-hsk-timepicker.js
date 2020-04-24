(function ($) {
  Drupal.behaviors.timepicker = {
    attach: function (context, settings) {

      $('form#node-event-form input#edit-field-event-date-0-value-time').timepicker({
        amPmText: [' ', ' '],
        defaultTime: '12:00'
      });


    } // end of attach function
  };
})(jQuery);