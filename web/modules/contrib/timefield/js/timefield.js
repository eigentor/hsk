/**
 * @file
 * Javascript for Timefield.
 */
(function ($) {
    Drupal.behaviors.timefield = {
      attach: function (context, settings) {
        // Iterate over timefield settings, which keyed by input class.
        for (var element in drupalSettings.timefield) {
          // Attach timepicker behavior to each matching element.
          $("input.edit-timefield-timepicker." + element, context).each(function (index) {console.log(settings.timefield[element]);
            $(this).timepicker(settings.timefield[element]);
          });
        }
      }
    };
})(jQuery);
