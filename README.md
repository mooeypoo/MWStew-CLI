[![Build Status](https://travis-ci.org/mooeypoo/MWStew.svg?branch=master)](https://travis-ci.org/mooeypoo/MWStew-CLI)
[![Coverage Status](https://coveralls.io/repos/github/mooeypoo/MWStew-CLI/badge.svg?branch=master)](https://coveralls.io/github/mooeypoo/MWStew-CLI?branch=master)
[![GitHub license](https://img.shields.io/badge/license-GPLv2-blue.svg?style=plastic)](https://raw.githubusercontent.com/mooeypoo/MWStew-CLI/master/LICENSE)

# MWStew-CLI: A command line tool to create mediawiki extension files

## Usage

Get the package from packagist:

```
composer install mooeypoo/mwstew-cli
```

To create extension files, run the `create-extension` command:

```
php ./vendor/bin/mwtsew create-extension extensionName
```

By default, files will be created in the path `./extensions/`. You can provide a different path by using the `--path [new path]` command.

To see the available parameters for create-extension command, use `./vendor/bin/mwstew create-extension -h`

## Contribute

This is fully open source tool. Pull requests are welcome! Please participate and help make this a great tool!

If you have suggestions or bug reports, please [submit an issue](https://github.com/mooeypoo/MWStew-CLI/issues).

If you want to contribute to the code, clone and initialize locally:

1. Clone the repo
2. Run `composer install`
3. Run `composer run test` to run tests

* See [MWStew](https://github.com/mooeypoo/MWStew) for the graphical interface.
* See [MWStew-CLI](https://github.com/mooeypoo/MWStew-builder) for the extension-creation engine behind this tool.

## Authors
Moriel Schottlender (mooeypoo)
