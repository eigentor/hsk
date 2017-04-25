CONTENTS OF THIS FILE
---------------------
   
 * Introduction
 * Requirements
 * Installation
 * Maintainers

INTRODUCTION
------------

Quick Node Clone is meant as a way in Drupal 8 to clone nodes. It currently supports cloning of most field types including [Inline Entity Form](https://www.drupal.org/project/inline_entity_form) and [Field Collection](https://www.drupal.org/project/field_collection).

The module adds a "Clone" tab to a node. When clicked, a new node is created and fields from the previous node are populated into the new fields.

This is potentially duplicate work of [Node Clone](https://www.drupal.org/project/node_clone), but as of release they don't have a stable D8 version and this code was created for a project from scratch in a reusable manner. This is focused on supporting more variety in field types than core's default cloning.

Future @TODO: Support more than just nodes! It could be expanded to all Content Entities fairly easily. This will likely be in its own properly named module with a better method for adding a UI to other content entities.


REQUIREMENTS
------------

This module requires the following modules:

 * Node


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.
 * Visit a node view page to clone it with the Clone tab.


MAINTAINERS
-----------

Current maintainers:
 * David Lohmeyer (Vilepickle) - https://www.drupal.org/user/783006
