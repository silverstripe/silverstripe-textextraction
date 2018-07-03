# Developer documentation

## Usage

Manual extraction via string file path:

```php
$myFile = '/my/path/myfile.pdf';
$extractor = FileTextExtractor::for_file($myFile);
$content = $extractor->getContent($myFile);
```

Manual extraction via File object:

```php
$myFile = File::get()->filter(['Name' => 'My file')->first();
$extractor = FileTextExtractor::for_file($myFile);
$content = $extractor->getContent($myFile);
```

Extraction with `FileTextExtractable` extension applied:

```php
$myFileObj = File::get()->First();
$content = $myFileObj->getFileContent();
```

This content can also be embedded directly within a template.

```
$MyFile.FileContent
```
