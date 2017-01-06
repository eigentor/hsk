(function ($) {
  Drupal.behaviors.hsk_js_tabs = {
    attach: function (context, settings) {

      $('.page-node-type-turnier #info-tabs').once('tabcontainer').each(function() {
        $(this).before('<div id="table-tabs"></div>');

        $('.page-node-type-turnier #info-tabs > .field').each(function(index, element){
          $(this).find('.field__label').clone().removeClass('field__label').addClass('tab-' + index).appendTo('#table-tabs');
          $(this).addClass('tab-' + index)
        });
      })



    }
  };
})(jQuery);