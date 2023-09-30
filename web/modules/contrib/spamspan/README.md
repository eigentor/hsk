# SpamSpan filter

The SpamSpan module obfuscates email addresses to help prevent spambots from
collecting them. It is based on the technique from
[spamspan](http://www.spamspan.com) but has undergone major modifications, as
the original spamspan code hasn't been updated since 2007.

The problem with most email address obfuscators is, that they rely upon
JavaScript being enabled on the client side. This makes the technique
inaccessible to people with screen readers. SpamSpan however will produce
clickable links if JavaScript is enabled, and will show the email address as
`example [at] example.com` if the browser does not support
JavaScript or if JavaScript is disabled.

This technique is unlikely to be absolutely foolproof. It is possible in theory
for a determined spambot to harvest addresses from your site, no matter how you
disguise them. But research suggests that by far the great majority of spambots
do not bother to attempt to collect addresses which have been hidden using
JavaScript.

## Requirements

This module requires no modules outside of Drupal core.

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

1. Go to the Extend page (`/admin/modules`), and enable the
   spamspan module (under Input Filters)

2. Go to the Text Formats and Editors page (`/admin/config/content/formats`)
   and configure the desired input formats to enable the filter.

3. (optional) Set available options under "Filter Settings".

## Bugs

- Please report any bugs using the bug tracker at
  [Bugs](http://drupal.org/project/issues/spamspan)

## Maintainers

- Julian Pustkuchen - [Anybody](https://www.drupal.org/u/anybody)
- vitalie - [vitalie](https://www.drupal.org/u/vitalie)
- lakka - [lakka](https://www.drupal.org/u/lakka)
- peterx - [peterx](https://www.drupal.org/u/peterx)
- Thomas Frobieter - [thomas.frobieter](https://www.drupal.org/u/thomasfrobieter)
- Joshua Sedler - [Grevil](https://www.drupal.org/u/grevil)
