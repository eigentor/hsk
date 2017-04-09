(function ($) {
  Drupal.behaviors.hsk_colorbox = {
    attach: function (context, settings) {

      $('.node .field--name-field-gallery-above-body .field-collection-item--name-field-gallery-images a').attr('rel', 'gallery-top');

      $('.node .field--name-field-gallery-below-body .field-collection-item--name-field-gallery-images a').attr('rel', 'gallery-bottom');

      // Open Image gallery links in fancybox
      $('.node .paragraph--type--image-gallery .field-collection-item--name-field-gallery-images a').colorbox({
        maxWidth: '97%',
        maxHeight: '97%',
      });
    }
  };
})(jQuery);
