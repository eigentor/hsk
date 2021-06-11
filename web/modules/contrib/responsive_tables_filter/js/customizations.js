/**
 * @file
 * Customizations to original Tablesaw library.
 *
 * If and when fixes are applied upstream, these changes can be removed.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.responsive_tables_filter = Drupal.responsive_tables_filter || {};
  Drupal.behaviors.facetsCheckboxReset = {
    attach: function (context) {
      Drupal.responsive_tables_filter.fixCellLabels(context);
    }
  };

  /**
   * Find all Tablesaw-generated cell labels.
   */
  Drupal.responsive_tables_filter.fixCellLabels = function (context) {
    var $labels = $('b.tablesaw-cell-label');
    $labels.each(Drupal.responsive_tables_filter.fixLabel);
  };

  /**
   * Add aria-hidden attribute.
   */
  Drupal.responsive_tables_filter.fixLabel = function () {
    $(this).attr('aria-hidden', true);
  };

})(jQuery, Drupal);