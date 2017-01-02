(function ($) {
  Drupal.behaviors.menu = {
    attach: function (context, settings) {


      $('ul#main-menu > li > a').click(function(){
          window.location = $(this).attr('href');
      });

      $('#main-menu').smartmenus({
        showTimeout: 100,
        hideTimeout: 100

      });

      //Mobile menu toggle
      $('.navbar-toggle').once('slideonce').each(function(){
        $(this).click(function(){
          $('.region-primary-menu').slideToggle();
        });
      });

      //Mobile dropdown menu
      if ( $(window).width() < 767) {
        $(".region-primary-menu li a:not(.has-submenu)").click(function () {
          $('.region-primary-menu').hide();
        });
      }
    }
  };
})(jQuery);