# Timefield

## Summary

Timefield is a Field API field that can store 3 types of time values: a simple
time value, a start time and end time, or a combination of these two with a
basic weekly repeat value.  The values are stored in the database as integer
offsets from 12AM.  If the second value continues into the next day,
i.e. 8PM - 2AM, the second value is stored as an offset +1 day.

### How to install

#### The non-composer way

1. Download this module
2. [Download jQuery UI Timepicker](https://github.com/fgelinas/timepicker) library and place it in the
   libraries folder.
   jQuery UI Timepicker library folder name
   should be 'timepicker'.
3. Install timefield the [usual way](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).
4. Add the timefield field to required entity and configure as per requirement.

#### The composer way 1

Run `composer require wikimedia/composer-merge-plugin`

Update the root `composer.json` file. For example:

```
   "extra": {
       "merge-plugin": {
           "include": [
               "web/modules/contrib/timefield/composer.libraries.json"
           ]
       }
   }
```


Run `composer require drupal/timefield fgelinas/timepicker`, the jQuery UI Timepicker will be
installed to the `libraries` folder automatically.

#### The composer way 2

Copy the following into the root `composer.json` file's `repository` key

```
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "fgelinas/timepicker",
                "version": "dev-master",
                "type": "drupal-library",
                "dist": {
                    "url": "https://github.com/fgelinas/timepicker/archive/refs/heads/master.zip",
                    "type": "zip"
                }
            }
        }
    ]
```

Run `composer require drupal/timefield fgelinas/timepicker`, the jQuery UI Timepicker library
will be installed to the `libraries` folder automatically as well.

## Features

If you have repeating events that occur regularly on a weekly basis and wish to provide a simple way to display and store this logic, this field may provide the functionality you seek.

There are 3 storage options:
1. Single Time Value
2. Start Time - End Time
3. Start Time (optional End Time), Weekday Repeat

There are a few display options as well:
1. List Display
2. Duration Display (for times with End Time)
3. Table Display (for weekly repeat options)
