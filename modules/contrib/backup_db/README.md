This module provides database export functionality with help from a php version of mysqldump. The library (https://github.com/ifsnop/mysqldump-php) supports the following features.

 - output binary blobs as hex.
 - resolves view dependencies (using Stand-In tables).
 - output compared against original mysqldump. Linked to travis-ci testing system.
 - dumps stored procedures.
 - does extended-insert and/or complete-insert.
 
This module depends upon Composer Manager to install the mysqldump-php library; please follow the installation section below.

Installation (@ref, http://cgit.drupalcode.org/address/tree/README.md)

1. cd / navigate to the Drupal root directory

2. add the Drupal Packagist repository
   ``
   composer config repositories.drupal composer https://packages.drupal.org/8
   ``

3. use composer to download the module and it's dependencies
  ``
  composer require drupal/backup_db
  ``

4. enable the Backup Database module.

More information
 - https://www.drupal.org/node/2405811
 
Credits
 - ifsnop (https://github.com/ifsnop)
 