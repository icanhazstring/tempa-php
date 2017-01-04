# icanhazstring/tempa-php

[![Build Status](https://api.travis-ci.org/icanhazstring/tempa-php.svg?branch=master)](https://travis-ci.org/icanhazstring/tempa-php)

## What for?
You all came across some `*.dist` or `*.skel` files of some sort. This library can be used to replace every existing substitute in those files as well as simply list them.

## Install
To install this library simply use composer.
```bash
$ composer require icanhazstring/tempa-php
```

After you have done this you need to create a `tempa.json` file inside you project. This file holds some basic information about the template files.

Here is an example
```json
{
  "fileExtensions": ["dist", "skel"],
  "prefix": "{$",
  "suffix": "}"
}
```

## Usage

### Scan
You can scan a directory or single file using the scan command.

```bash
$ vendor/bin/tempa file:scan <dir> [<config>]
```

You can deliver every other config you want. By default the script will take the `tempa.json` from the location you executed the script.

```bash
$ vendor/bin/tempa file:scan test/Demo
$ vendor/bin/tempa file:scan test/Demo test/Demo/tempa.json

Scanning for template files in: /home/icanhazstring/tempa-php/test/Demo
=======================================================================

 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%


/home/ando/tempa-php/test/Demo/Sub/test.php.dist
------------------------------------------------

Line 3 : 'database' => '{$database}',
Line 4 : 'username' => '{$user}'

/home/ando/tempa-php/test/Demo/test.php.dist
--------------------------------------------

Line 3 : 'placeholder' => '{$superAwesome}'
```

### Substitute

To replace stuff run the following:

```bash
$ vendor/bin/tempa file:substitute [-f|--mapfile [MAPFILE]] [--] <dir> [<config>] [<map>]... 
```

```bash
$ vendor/bin/tempa file:substitute test/Demo/
$ vendor/bin/tempa file:substitute test/Demo/ --mapfile=test/Demo/maps/map.[json|php]
$ vendor/bin/tempa file:substitute test/Demo/ test/Demo/tempa.json database=localhost user=icanhazstring superAwesome=mega 

Processing template files in: /home/icanhazstring/tempa-php/test/Demo
=====================================================================

 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
```

### Interactive

You can replace every with an interactive mode:

```bash
$ vendor/bin/tempa file:interactive test/Demo
$ vendor/bin/tempa file:interactive test/Demo test/Demo/tempa.json


Interactive substitution for template files in: /home/icanhazstring/tempa-php/test/Demo
=======================================================================================

Found 3 substitutes
-------------------

database:
>

...
```
