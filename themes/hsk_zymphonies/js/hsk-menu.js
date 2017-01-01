(function ($) {
  Drupal.behaviors.menu = {
    attach: function (context, settings) {
      $('#main-menu').smartmenus({
        showTimeout: 100,
        hideTimeout: 100

      });

      //Mobile menu toggle
      $('.navbar-toggle').click(function(){
        $('.region-primary-menu').slideToggle();
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