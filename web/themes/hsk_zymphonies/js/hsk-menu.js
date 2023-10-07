(function ($) {
  Drupal.behaviors.menu = {
    attach: function (context, settings) {

      // Keep the parent menu item active (add the class "is-active" to the anchor alement).


      // $('ul#main-menu > li > a').click(function(){
      //     window.location = $(this).attr('href');
      // });

      $('#block-hsk-zymphonies-main-menu > ul.menu').addClass('sm')
        .addClass('menu-base-theme');
        
      $(once('smartmenu', '#block-hsk-zymphonies-main-menu > ul.menu')).smartmenus({
        showTimeout: 100,
        hideTimeout: 100
      });

      //Mobile menu toggle
      $(once('slideonce', '.navbar-toggle')).each(function(){
        $(this).click(function(){
          $('.region-primary-menu').slideToggle();
        });
      });

      // Position the chess pieces background image dynamically
      // on every page where the proper h1 exists (not in front page)
      if($('h1.js-quickedit-page-title').length != 0) {
        var startheading = $('h1.js-quickedit-page-title').offset();
        var widthheading = $('h1.js-quickedit-page-title').innerWidth();
        var endheading = startheading.left + widthheading;
        var offset_pieces = endheading + 50;
        $('#page-title').css('background-position', offset_pieces);
      }

      $( window ).resize(function() {
        var startheading = $('h1.js-quickedit-page-title').offset();
        var widthheading = $('h1.js-quickedit-page-title').innerWidth();
        var endheading = startheading.left + widthheading;
        var offset_pieces = endheading + 50;
        $('#page-title').css('background-position', offset_pieces);

      });



}
  };
})(jQuery);
