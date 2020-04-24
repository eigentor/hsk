(function ($) {
  Drupal.behaviors.pgn_delay = {
    attach: function(context, settings) {

      setTimeout(function(){
        $.getScript('https://pgn.chessbase.com/cbreplay.js');
      }, 2000);
    } // end of attach function
  };
})(jQuery);
