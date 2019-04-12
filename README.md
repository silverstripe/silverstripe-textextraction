# Text extraction module

[![Build Status](https://secure.travis-ci.org/silverstripe/silverstripe-textextraction.png)](http://travis-ci.org/silverstripe/silverstripe-textextraction)
[![Code Quality](http://img.shields.io/scrutinizer/g/silverstripe/silverstripe-textextraction.svg?style=flat)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-textextraction)
[![Version](http://img.shields.io/packagist/v/silverstripe/textextraction.svg?style=flat)](https://packagist.org/packages/silverstripe/silverstripe-textextraction)
[![License](http://img.shields.io/packagist/l/silverstripe/textextraction.svg?style=flat)](license.md)


Provides a text extraction API for file content, that can hook into different extractor
engines based on availability and the parsed file format. The output returned is always a string of the file content.

Via the `FileTextExtractable` extension, this logic can be used to
cache the extracted content on a `DataObject` subclass (usually `File`).

The module supports text extraction on the following file formats:

 * HTML (built-in)
 * PDF (with XPDF or Solr)
 * Microsoft Word, Excel, Powerpoint (Solr)
 * OpenOffice (Solr)
 * CSV (Solr)
 * RTF (Solr)
 * EPub (Solr)
 * Many others (Tika)

## Requirements

 * SilverStripe ^3.7
   * For SilverStripe 4 support, use silverstripe/textextraction ^3.0
   * For SIlverStripe 3.1-3.7 support, use silverstripe/textextraction ~2.1.0
 * (optional) [XPDF](http://www.foolabs.com/xpdf/) (`pdftotext` utility)
 * (optional) [Apache Solr with ExtracingRequestHandler](http://wiki.apache.org/solr/ExtractingRequestHandler)
 * (optional) [Apache Tika](http://tika.apache.org/)

## Installation

```
composer require silverstripe/textextraction
```

The module depends on the [Guzzle HTTP Library](http://guzzlephp.org),
which is automatically checked out by composer. Alternatively, install Guzzle
through PEAR and ensure its in your `include_path`.

## Documentation

 * [Configuration](docs/en/configuration.md)
 * [Developer documentation](/docs/en/developer-docs.md)

## Bugtracker

Bugs are tracked in the issues section of this repository. Before submitting an issue please read over
existing issues to ensure yours is unique.

If the issue does look like a new bug:

 - Create a new issue
 - Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots
  and screencasts can help here.
 - Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version,
 Operating System, any installed SilverStripe modules.

Please report security issues to security@silverstripe.org directly. Please don't file security issues in the bugtracker.

## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss
 with the module maintainers.
