[![Build Status](https://travis-ci.org/mooeypoo/MWStew.svg?branch=master)](https://travis-ci.org/mooeypoo/MWStew-CLI)
[![GitHub license](https://img.shields.io/badge/license-GPLv2-blue.svg?style=plastic)](https://raw.githubusercontent.com/mooeypoo/MWStew-CLI/master/LICENSE)

# MWStew-CLI: A command line tool to create mediawiki extension files

## Development
This tool is fairly stable, but is currently going through QA and testing, and is continuously developed. Please report any bugs you encounter!

**Feel free to contribute!**

## Usage

Get the package from packagist:

```
composer install mooeypoo/mwstew-cli
```

To create extension files, run the `create-extension` command:

```
php ./vendor/bin/mwtsew create-extension extensionName
```

By default, files will be created in the path `./extensions/extensionName`. You can provide a different path by using the `--path [new path]` command.

**Parameter descriptions coming soon**

## Development
If you want to contribute, clone and initialize locally:

1. Clone the repo
2. Run `composer install`
3. Run `composer run test` to run tests

* See [MWStew](https://github.com/mooeypoo/MWStew) for the graphical interface.
* See [MWStew-builder](https://github.com/mooeypoo/MWStew-builder) for the base package that builds the extension files.

## Contribute
This is fully open source tool. It will be hosted so anyone that wants to use it can do so without running the script.

Pull requests are welcome! Please participate and help make this a great tool!

## Authors
Moriel Schottlender (mooeypoo)
