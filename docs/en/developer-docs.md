# Developer documentation
## Usage

Manual extraction:

```php
$myFile = '/my/path/myfile.pdf';
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
