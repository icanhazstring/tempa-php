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
$ vendor/bin/tempa file:scan [--config|c [CONFIG]] [--] <FILE|DIRECTORY>
```

You can deliver every other config you want. By default the script will take the `tempa.json` from the location you executed the script.

```bash
$ vendor/bin/tempa file:scan --config=tempa.json test/Demo

Scanning for template files in: /home/ando/tempa-php/test/Demo
==============================================================

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
$ vendor/bin/tempa file:scan [--config|c [CONFIG]] [--] <FILE|DIRECTORY> [<map>]... 
```

```bash
$ vendor/bin/tempa file:substitute --config=test/Demo/tempa.json test/Demo/ database=localhost user=icanhazstring superAwesome=mega 

Processing template files in: /home/ando/tempa-php/test/Demo
============================================================

 2/2 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
```
