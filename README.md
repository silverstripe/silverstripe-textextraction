# Text extraction module

[![CI](https://github.com/silverstripe/silverstripe-textextraction/actions/workflows/ci.yml/badge.svg)](https://github.com/silverstripe/silverstripe-textextraction/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

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

## Installation

```sh
composer require silverstripe/textextraction
```

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
 - Describe your environment as detailed as possible: Silverstripe version, Browser, PHP version,
 Operating System, any installed Silverstripe modules.

Please report security issues to security@silverstripe.org directly. Please don't file security issues in the bugtracker.

## Development and contribution
If you would like to make contributions to the module please ensure you raise a pull request and discuss
 with the module maintainers.
