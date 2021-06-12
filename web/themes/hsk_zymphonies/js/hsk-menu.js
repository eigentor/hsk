(function ($) {
  Drupal.behaviors.menu = {
    attach: function (context, settings) {

      // Keep the parent menu item active (add the class "is-active" to the anchor alement).


      // $('ul#main-menu > li > a').click(function(){
      //     window.location = $(this).attr('href');
      // });

      $('#block-hsk-zymphonies-main-menu > ul.menu').addClass('sm')
        .addClass('menu-base-theme').smartmenus({
        showTimeout: 100,
        hideTimeout: 100
      });

      //Mobile menu toggle
      $('.navbar-toggle').once('slideonce').each(function(){
        $(this).click(function(){
          $('.region-primary-menu').slideToggle();
        });
      });
      //
      // //Mobile dropdown menu
      // if ( $(window).width() < 767) {
      //   $(".region-primary-menu li a:not(.has-submenu)").click(function () {
      //     $('.region-primary-menu').hide();
      //   });
      // }

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

      // Hide empty Views columns in the DZW List
    //   $('.view-dwz-list table tr th').each(function(i) {
    //   //select all tds in this column
    //   var tds = $(this).parents('table')
    //     .find('tr td:nth-child(' + (i + 1) + ')');
    //   //check if all the cells in this column are empty
    //   if(tds.length == tds.filter(':empty').length) {
    //     //hide header
    //     $(this).hide();
    //     //hide cells
    //     tds.hide();
    //   }
    // });

}
  };
})(jQuery);
