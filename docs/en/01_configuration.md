---
title: Configuration
summary: Configuration options, including enabling extraction for DataObjects, managing cached content length, swapping cache backends, and configuring PDF text extraction
icon: cog
---

# Configuration

By default, only extraction from HTML documents is supported.
No configuration is required for that, unless you want to make
the content available through your [`DataObject`](api:SilverStripe\ORM\DataObject) subclass.
In this case, add the following to `app/_config/config.yml`:

```yml
SilverStripe\Assets\File:
  extensions:
    - SilverStripe\TextExtraction\Extension\FileTextExtractable
```

By default any extracted content will be cached against the database row. In order to stay within common size
constraints for SQL queries required in this operation, the cache sets a maximum character length after which
content gets truncated (default: `500000`). You can configure this value through
[`Database.max_content_length`](api:SilverStripe\TextExtraction\Cache\FileTextCache\Database->max_content_length) in your YAML configuration.

Alternatively, extracted content can be cached using [`Cache`](api:SilverStripe\TextExtraction\Cache\FileTextCache\Cache) to prevent excessive database growth.
In order to swap out the cache backend you can use the following YAML configuration.

```yml
---
Name: mytextextraction
After: '#textextraction'
---
SilverStripe\Core\Injector\Injector:
  SilverStripe\TextExtraction\Cache\FileTextCache:
    class: SilverStripe\TextExtraction\Cache\FileTextCache\Cache

SilverStripe\TextExtraction\Cache\FileTextCache\Cache:
  lifetime: 3600 # Number of seconds to cache content for
```

## `XPDF`

PDFs require special handling, for example through the [XPDF](http://www.xpdfreader.com/)
command-line utility. Follow their installation instructions, its presence will be automatically
detected for \*nix operating systems. You can optionally set the binary path (required for Windows) in `app/_config/config.yml`:

```yml
SilverStripe\TextExtraction\Extractor\PDFTextExtractor:
  binary_location: /my/path/pdftotext
```
