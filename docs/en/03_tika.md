---
title: Tika
summary: Using Apache Tika for text extraction, using either CLI or REST server configurations
icon: file-alt
---

# Tika

Support for Apache Tika (1.8 and above) is included. This can be run in one of two ways: Server or CLI.

See [the Apache Tika home page](https://tika.apache.org/) for instructions on installing and
configuring this. Download the latest `tika-app` for running as a CLI script, or `tika-server` if you're planning
to have it running constantly in the background. Starting tika as a CLI script for every extraction request
is fairly slow, so we recommend running it as a server.

This extension will best work with the [fileinfo PHP extension](http://php.net/manual/en/book.fileinfo.php)
installed to perform mime detection. Tika validates support via mime type rather than file extensions.

## CLI

Ensure that your machine has a `tika` command available which will run the CLI script.

```bash
#!/bin/bash
exec java -jar tika-app-1.8.jar "$@"
```

## REST server

Tika can also be run as a server. You can configure your server endpoint by setting the URL via config.

```yml
SilverStripe\TextExtraction\Extractor\TikaServerTextExtractor:
  server_endpoint: 'http://localhost:9998'
```

Alternatively this may be specified via the `SS_TIKA_ENDPOINT` environment variable in your `.env` file, or an
environment variable of the same name.

Then start up your server as below:

```bash
java -jar tika-server-1.8.jar --host=localhost --port=9998
```

While you can run `tika-app-1.8.jar` in server mode as well (with the `--server` flag),
it behaves differently and is not recommended.

The module will log extraction errors with PSR-3 "notice" priority by default,
for example a "422 Unprocessable Entity" HTTP response for an encrypted PDF.
In case you want more information on why processing failed, you can increase
the logging verbosity in the tika server instance by passing through
an `--includeStack` flag. Logs can be passed on to files or external logging services,
see [error handling](http://doc.silverstripe.org/en/developer_guides/debugging/error_handling)
documentation for Silverstripe core.
