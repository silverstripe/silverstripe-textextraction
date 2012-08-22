# Text Extraction Module

## Overview

Provides an extraction API for file content, which can hook into different extractor
engines based on availability and the parsed file format.
The output is always a string: the file content.

Via the `FileTextExtractable` extension, this logic can be used to 
cache the extracted content on a `DataObject` subclass (usually `File`).

Note: Previously part of the [sphinx module](https://github.com/silverstripe/silverstripe-sphinx).

## Requirements

 * SilverStripe 3.0
 * (optional) [XPDF](http://www.foolabs.com/xpdf/) (`pdftotext` utility)

## Configuration

No configuration is required, unless you want to make
the content available through your `DataObject` subclass.
In this case, add the following to `mysite/_config.php`:

	DataObject::add_extension('File', 'FileTextExtractable');

## Usage

Manual extraction:

	$myFile = '/my/path/myfile.pdf';
	$extractor = FileTextExtractor::for_file($myFile);
	$content = $extractor->getContent($myFile);

DataObject extraction:

	$myFileObj = File::get()->First();
	$content = $myFileObj->extractFileAsText();