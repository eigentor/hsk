(function ($) {
  Drupal.behaviors.jjv_tooltips = {
    attach: function(context, settings) {

      $.getScript('https://pgn.chessbase.com/cbreplay.js');

    } // end of attach function
  };
})(jq300);
