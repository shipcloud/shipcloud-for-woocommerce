# PDFMerger for PHP 7

Original written by http://pdfmerger.codeplex.com/team/view

Forked from https://github.com/clegginabox/pdf-merger

## Installation

```composer require jmleroux/pdf-merger```

## Example Usage

```php
<?php
use Jmleroux\PDFMerger\PDFMerger;

$pdf = new PDFMerger();

$pdf->addPDF('samplepdfs/one.pdf', '1, 3, 4');
$pdf->addPDF('samplepdfs/two.pdf', '1-2');
$pdf->addPDF('samplepdfs/three.pdf', 'all');

//You can optionally specify a different orientation for each PDF
$pdf->addPDF('samplepdfs/one.pdf', '1, 3, 4', 'L');
$pdf->addPDF('samplepdfs/two.pdf', '1-2', 'P');

$pdf->merge('file', 'samplepdfs/TEST2.pdf', 'P');
```

### PDF libraries

You can use either FPDF, TCPDF or tFPDF as the internal PDF library
by providing the right parameter to the constructor:
 
```php
<?php
use Jmleroux\PDFMerger\PDFMerger;

$pdf = new PDFMerger(); // use FPDF
$pdf = new PDFMerger('fpdf'); // use FPDF

$pdf = new PDFMerger('tcpdf'); // use TCPDF

$pdf = new PDFMerger('tfpdf'); // use tFPDF
```

### Output modes

This merger uses verbose parameter names for the various pdf output modes common to the three libraries:
 
* `browser` (default): send the pdf binary to the browser. The borwser PDF plug-in is used if available.
* `download`: send the pdf to the browser and force a file download with the name given.
* `string`: outputs the raw binary string.
* `file`: save to a local server file with the name given.

## Development

This repo is shipped with a docker-compose file so that you don't need a local version of PHP.

Use make commands to install and run tests:

To install dependencies:

```
make vendor
```

To run tests:

```
make tests
```

### WTF?

Yes, why the fork?

I first made a PR to fix the "slice feature", but I figured out I wanted a library with tests and tags.
Plus, I wanted to add docker-compose because I do not have any local PHP installed anymore,
so I decided to fork it and start with PHP 7. 
