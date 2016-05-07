(function ($) {
  Drupal.behaviors.header_slideshow = {
    attach: function (context, settings) {


$('.path-frontpage .view-id-header_slideshow .view-content').slick({
  infinite: true,
  autoplay: true,
  arrows: false,
  autoplaySpeed: 6000,
  speed: 700,
  slidesToShow: 1,
  slidesToScroll: 1,
  slidesToScroll: 1,
  slide: 'div.views-row',
});
      

    } // end of attach function
  };
})(jQuery);