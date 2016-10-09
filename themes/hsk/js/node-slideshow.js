(function ($) {
  Drupal.behaviors.node_slideshow = {
    attach: function (context, settings) {

$('.page-node-type-article .field--name-field-nw-image-gallery').slick({
  infinite: true,
  arrows: true,
  speed: 700,
  fade: true,
  slidesToShow: 1,
  slidesToScroll: 1,
  slidesToScroll: 1,
  slide: 'div.field__item',
});

// Do not get portrait images get too big
var portrait_image_sizing = function() {
  window_width = $(document).width();
  content_raw_width = $('.layout-container main .region-content').width();
  if(content_raw_width < 480) {
    arrow_position = (content_raw_width / 2) - 60;
  } else {
    arrow_position = (content_raw_width / 2) - 85;
  }

  if(window_width >= 480) {
    var content_width = $('.layout-container main .region-content').width() - 70;
  } else {
    var content_width = $('.layout-container main .region-content').width() - 40;
  }
  // Set the position of the slider arrows
  if(window_width < 650) {
    $('.page-node-type-article .field--name-field-nw-image-gallery .slick-prev')
      .add('.page-node-type-article .field--name-field-nw-image-gallery .slick-next').css({'top' : arrow_position + 'px'});
  }
$('.page-node-type-article .field--name-field-nw-image-gallery .field__item img').css({'max-height' : content_width + 'px', 'width' : 'auto'});
}
// run the function
portrait_image_sizing();

$(window).resize(function(){
  portrait_image_sizing();
});


// Set the Caption of the images to the same width as the image
var set_caption_width = function() {
    $('.page-node-type-article .field--name-field-nw-image-gallery .field__item img').each(function(){
      var image_width = $(this).width();
      matching_caption = $(this).parents('.field__item').find('.field--name-field-nw-gal-img-caption');
      $(matching_caption).outerWidth(image_width);
  });
}
$('.paragraphs-item-p-image-file-slider').waitForImages(function() {
 set_caption_width();
});

$(window).resize(function(){
  $('.paragraphs-item-p-image-file-slider').waitForImages(function() {
    set_caption_width();
  });
});



    } // end of attach function
  };
})(jQuery);