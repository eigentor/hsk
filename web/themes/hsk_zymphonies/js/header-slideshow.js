(function ($) {
  Drupal.behaviors.header_slideshow = {
    attach: function (context, settings) {



      $('.path-frontpage #block-views-block-header-slideshow-block-1 .view-content').slick({
        infinite: true,
        autoplay: true,
        arrows: true,
        autoplaySpeed: 6000,
        speed: 700,
        slidesToShow: 1,
        slidesToScroll: 1,
        slidesToScroll: 1,
        slide: 'div.views-row',
      });


      // $('.path-frontpage #block-views-block-header-par-slideshow-block-1 .view-content').slick({
      //   infinite: true,
      //   autoplay: true,
      //   arrows: true,
      //   autoplaySpeed: 6000,
      //   speed: 700,
      //   slidesToShow: 1,
      //   slidesToScroll: 1,
      //   slidesToScroll: 1,
      //   slide: 'div.views-row',
      // });

      $('.path-frontpage #block-headerslideshow .block').slick({
        infinite: true,
        autoplay: true,
        arrows: true,
        autoplaySpeed: 6000,
        speed: 700,
        slidesToShow: 1,
        slidesToScroll: 1,
        slidesToScroll: 1,
        slide: 'div.paragraph--type--slideshow-image',
      });

      // Make the height of the Slider adapt to the height of the current slide on mobile.
      // This way there is no etmpty space below if a slide is lower than the others.
      $slickSlider = $('.path-frontpage #block-views-block-header-par-slideshow-block-1 .view-content');

      var sliderAdaptiveHeightMobile = function() {
        $slickSlider.find('.slick-slide').height('0');
        $slickSlider.find('.slick-slide.slick-active').height('auto');
        $slickSlider.find('.slick-list').height('auto');
        $slickSlider.slick('setOption', null, null, true);
      }

      sliderAdaptiveHeightMobile();

      $slickSlider.on('afterChange', function(event, slick, currentSlide, nextSlide){
        sliderAdaptiveHeightMobile();
      });


    } // end of attach function
  };
})(jQuery);
