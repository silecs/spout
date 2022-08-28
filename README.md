# Spout

Spout is a PHP library to read and write spreadsheet files (CSV, XLSX and ODS), in a fast and scalable way.
Unlike other file readers or writers, it is capable of processing very large files, while keeping the memory usage really low (less than 3MB).

This library is a fork of [Box's Spout library](https://github.com/box/spout) where development has stopped.
Its development is focused on performance and safety (prefer less feature to more bugs).

## Documentation

Full documentation can be found at [https://opensource.box.com/spout/](https://opensource.box.com/spout/).

## Requirements

* PHP version 7.3 or higher
* PHP extension `php_zip` enabled
* PHP extension `php_xmlreader` enabled

## Upgrade guide

See the [changelog](CHANGELOG.md) for release notes.

Version 3 introduced new functionality but also some breaking changes. If you want to upgrade your Spout codebase from version 2 please consult the [Upgrade guide](UPGRADE-3.0.md). 

## Running tests

The `master` branch includes unit, functional and performance tests.
If you just want to check that everything is working as expected, executing the unit and functional tests is enough.

* `phpunit` - runs unit and functional tests
* `phpunit --group perf-tests` - only runs the performance tests (may take more than 10 minutes)

For more information on developing, see <CONTRIBUTING.md>.

## Copyright and License

Copyright 2022 Silecs
Copyright 2017 Box, Inc. All rights reserved.

This library is a fork of [Box's Spout library](https://github.com/box/spout).

Code added before the fork (up to 0739e044dafd45d750da5390d24a913d5e1ed3fc) is copyright of Box, Inc. and licensed under the [Apache License, Version 2.0](https://github.com/openspout/openspout/blob/0739e044dafd45d750da5390d24a913d5e1ed3fc/LICENSE).

Code added after the fork (descendants of ba8bc1299b9198c2f354a59fec905944337f300a) is licensed under [MIT License]().
