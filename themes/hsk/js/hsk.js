(function ($) {
  Drupal.behaviors.presseclub = {
    attach: function (context, settings) {

/**
 * Dropdown Menu Desktop
 */

// Add a class to each Menu item that has submenu items
$('nav#block-presseclub-main-menu > ul > li').has( "ul" ).addClass('has-submenu');
$('nav#block-presseclub-main-menu > ul > li').has( "ul" ).once('add_arrow').each(function(){
  $(this).children('a').append('<span class="arrow"></span>');
})

$('nav#block-presseclub-main-menu > ul > li').on("mouseover", function() {
  var window_width = $(document).width();
  if(window_width >= 1024) {
    setTimeout($.proxy(function () {
      $(this).addClass('menu-visible');
    }, this), 100);
  }
}).on("mouseleave", function() {
  var window_width = $(document).width();
  if(window_width >= 1024) {
    setTimeout($.proxy(function () {
      $(this).removeClass('menu-visible');
    }, this), 100);
  }
});

/**
 * Mobile Menu
 */

$('#block-mobilemenubutton a').once('toggle_menu').click(function(){
    $('nav#block-presseclub-main-menu').slideToggle();
});

// First expand the menu for Mobile without going to the link for Menu Items that
// have submenu items

$('nav#block-presseclub-main-menu > ul > li').has('ul').one('click', function(e) {
  var window_width = $(document).width();
  if(window_width < 1024 && $(this).not('.menu-visible')) {
    e.preventDefault();
    $(this).addClass('menu-visible');
  }
});

/**
 * Temporary Fix for Views Bug: hide labels for empty fields in
 * "Veranstaltungen" View
 */
$('.view-events .views-field-field-event-location').each(function(){
  if ($(this).children('.field-content').text().trim().length == 0) {
    $(this).hide();
  }
});


    } // end of attach function
  };
})(jQuery);
