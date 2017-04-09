(function ($) {
  Drupal.behaviors.hsk_fancybox = {
    attach: function (context, settings) {

      $('.node .field--name-field-gallery-above-body .field-collection-item--name-field-gallery-images a').attr('data-fancybox', 'gallery-top');

      // Open Image gallery links in fancybox
      $('.node .paragraph--type--image-gallery .field-collection-item--name-field-gallery-images a').fancybox({
        infobar: false,
        // buttons: false,
        smallBtn: 'auto',
        closeTpl : '<button data-fancybox-close class="fancybox-close-small"></button>',
      });
    }
  };
})(jQuery);
