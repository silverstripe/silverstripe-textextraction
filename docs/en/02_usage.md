---
title: Usage
summary: Various methods for text extraction, including extraction via file path or File object, and using the FileTextExtractable extension
icon: file-code
---

# Usage

## Manual extraction via string file path

```php
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;

$myFile = '/my/path/myfile.pdf';
$extractor = FileTextExtractor::for_file($myFile);
$content = $extractor->getContent($myFile);
```

## Manual extraction via `File` object

```php
use SilverStripe\Assets\File;
use SilverStripe\TextExtraction\Extractor\FileTextExtractor;

$myFile = File::get()->filter(['Name' => 'My file'])->first();
$extractor = FileTextExtractor::for_file($myFile);
$content = $extractor->getContent($myFile);
```

## Extraction with `FileTextExtractable`

The [`FileTextExtractable`](api:SilverStripe\TextExtraction\Extension\FileTextExtractable) extension can be applied and used for extraction:

```php
use SilverStripe\Assets\File;

$myFileObj = File::get()->first();
$content = $myFileObj->getFileContent();
```

## Embedding within a template

```text
$MyFile.FileContent
```
