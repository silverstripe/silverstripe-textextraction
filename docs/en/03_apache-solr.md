---
title: Apache Solr
summary: Apache Solr's role in text extraction using Apache Tika, its configuration, and content indexing
icon: search
---

# Apache solr

Apache Solr is a fulltext search engine, an aspect which is often used
alongside this module. But more importantly for us, it has bindings to [Apache Tika](http://tika.apache.org/)
through the [ExtractingRequestHandler](http://wiki.apache.org/solr/ExtractingRequestHandler) interface.
This allows Solr to inspect the contents of various file formats, such as Office documents and PDF files.
The textextraction module retrieves the output of this service, rather than altering the index.
With the raw text output, you can decide to store it in a database column for fulltext search
in your database driver, or even pass it back to Solr as part of a full index update.

In order to use Solr, you need to configure a URL for it (in `app/_config/config.yml`):

```yml
SilverStripe\TextExtraction\Extractor\SolrCellTextExtractor:
  base_url: 'http://localhost:8983/solr/update/extract'
```

> [!NOTE]
> In case you're using multiple cores, you'll need to add the core name to the URL
> (e.g. `http://localhost:8983/solr/PageSolrIndex/update/extract`).
> The ["fulltext" module](https://github.com/silverstripe/silverstripe-fulltextsearch)
> uses multiple cores by default, and comes prepackaged with a Solr server.
> It's a stripped-down version of Solr, follow the module README on how to add
> Apache Tika text extraction capabilities.

You need to ensure that some indexable property on your object
returns the contents, either by directly accessing `FileTextExtractable->extractFileAsText()`,
or by writing your own method around `FileTextExtractor->getContent()` (see "Usage" below).
The property should be listed in your `SolrIndex` subclass, e.g. as follows:

```php
namespace App\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;

class MyDocument extends DataObject
{
    private static $db = [
        'Path' => 'Text',
    ];

    public function getContent()
    {
        $extractor = FileTextExtractor::for_file($this->Path);
        return $extractor ? $extractor->getContent($this->Path) : null;
    }
}
```

```php
namespace App\Search;

use App\Models\MyDocument;
use SilverStripe\FullTextSearch\Solr\SolrIndex;

class MySolrIndex extends SolrIndex
{
    public function init()
    {
        $this->addClass(MyDocument::class);
        $this->addStoredField('Content', 'HTMLText');
    }
}
```

Extractors will return content formatted with new line characters at the end of each extracted line. If you want
this to be used in HTML content it may be worth wrapping the result in a `nl2br()` call before using it in your
code.

> [!NOTE]
> This isn't a terribly efficient way to process large amounts of files, since
> each HTTP request is run synchronously.
