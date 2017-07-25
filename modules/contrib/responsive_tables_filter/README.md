##Description

Enable the "Make tables responsive" text format filter to display field tables responsively using the [Tablesaw Library](https://www.filamentgroup.com/lab/tablesaw.html).

 * For a full description of the module, visit the project page:
   https://drupal.org/project/responsive_tables_filter

##Basic Usage

0. Place this module in your /modules directory and enable it.
1. Go to admin/config/content/formats.
2. Enable the filter "Responsive Tables Filter" on any text format for which you
want to make tables responsive (e.g., Basic HTML).
3. Verify the text format(s) allow HTML table tags. If you are using the standard
"Limit HTML tags" filter (admin/config/content/formats), all of the following
should be allowed:
`<table> <th> <tr> <td> <thead> <tbody> <tfoot>` (if you want class attributes
supplied by the user to co-exist, use the syntax `<table class>` here).

Any fields that use the text format(s) which have tables in them will now be
responsive.

The 8.x version of this module currently automatically makes Views tables responsive.

##FAQ

Q: Can I override the tablesaw CSS?

A: Yes, but any CSS you add needs to include the tablesaw naming patterns so that the Javascript can find elements.

Q: Can I target specific tables within nodes?

A: The Drupal 8 version of this module does not yet support xpath query.

Q: Can I do anything to have tablesaw not be applied to a table (WYSIWYG Only).

A: Yes! Add the class `no-tablesaw` to the table.

Current maintainers:
- Mark Fullmer (mark_fullmer) - https://www.drupal.org/u/mark_fullmer





