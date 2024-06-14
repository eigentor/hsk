CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Config Default Image provides a field formatter which allows a site builder
to specify the default image for an image field which is tracked in source
code and deployed in config management. It captures the path to a
VCS-controlled image file instead of the uuid of a non-tracked content
resource in site files.

This addresses the problem that the core default image feature uses a file
content which, without this module, config management (drush cex/cim...) will
deploy the config but not the content, so the default image wouldn't deploy
with it.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/config_default_image

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/search/config_default_image


REQUIREMENTS
------------

No special requirements.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
   For further information, see:
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules


CONFIGURATION
-------------

 Prepare image file:

  * Place a web-friendly image file that Drupal recognizes (png, jpg, etc) in
  a folder in your Drupal installation that is tracked by VCS. Suggested folders
  are a custom module or a custom theme folder, and copy the path to that file
  from Drupal root. Ex: /modules/custom/my_module/images/default_logo.png

 Field configuration:

  * In Manage Fields, edit that field: In this image field configuration,
  make sure that the Default Image is not set.
  * In Manage Display, configure the Format of this field: Paste the path
  to the image, and adjust any other options.
  * Be sure to save the Manage Display screen.

 Export configuration:
  * Export as you normally would: ie. `drush cex -y`
  * Capture to VCS both your updated field config (which contains the
  environment-universal path) and the image asset itself.

MAINTAINERS
-----------

Current maintainers:
 * Gaël Gosset (GaëlG) - drupal.org/u/gaëlg

This project has been sponsored by:
 * Insite
   A lasting and independent business project with more than 20 years
   experience and several hundred references in web projects and visual
   communication with public actors, associative and professional networks.
