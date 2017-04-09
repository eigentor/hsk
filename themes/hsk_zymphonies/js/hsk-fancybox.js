(function ($) {
  Drupal.behaviors.hsk_fancybox = {
    attach: function (context, settings) {

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
