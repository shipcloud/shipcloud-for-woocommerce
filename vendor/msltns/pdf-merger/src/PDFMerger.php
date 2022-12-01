<?php
/**
 *  PDFMerger created by Jarrod Nettles December 2009
 *  jarrod@squarecrow.com
 *
 * Class for easily merging PDFs (or specific pages of PDFs) together into one. Output to a file, browser, download, or
 * return as a string. Unfortunately, this class does not preserve many of the enhancements your original PDF might
 * contain. It treats your PDF page as an image and then concatenates them all together.
 *
 * Note that your PDFs are merged in the order that you provide them using the addPDF function, same as the pages.
 * If you put pages 12-14 before 1-5 then 12-15 will be placed first in the output.
 */

declare(strict_types=1);

namespace Jmleroux\PDFMerger;

use InvalidArgumentException;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\Tcpdf\Fpdi as FpdiTcpdf;
use setasign\Fpdi\Tfpdf\Fpdi as FpdiTFpdf;

class PDFMerger
{
    private const LIB_FPDF = 'fpdf';
    private const LIB_TCPDF = 'tcpdf';
    private const LIB_TFPDF = 'tfpdf';

    /** @var string */
    private $library;

    /**
     * ['form.pdf']  ["1,2,4, 5-19"]
     *
     * @var string[]
     */
    private $files;

    public function __construct(string $library = self::LIB_FPDF)
    {
        if (!in_array($library, [self::LIB_FPDF, self::LIB_TCPDF,self::LIB_TFPDF])) {
            throw new InvalidArgumentException(sprintf('Unknown Fpdi library provided: "%s"', $library));
        }

        $this->library = $library;
    }

    /**
     * Add a PDF for inclusion in the merge with a valid file path.
     * Pages should be formatted: 1,3,6, 12-16.
     */
    public function addPDF(string $filepath, string $pages = 'all', $orientation = null): self
    {
        if (file_exists($filepath)) {
            if (strtolower($pages) != 'all') {
                $pages = $this->rewritepages($pages);
            }

            $this->files[] = [$filepath, $pages, $orientation];
        } else {
            throw new InvalidArgumentException("Could not locate PDF on '$filepath'");
        }

        return $this;
    }

    /**
     * Merges your provided PDFs and outputs to specified location.
     *
     * @return string|bool
     */
    public function merge(
        string $outputmode = 'browser',
        string $outputpath = 'newfile.pdf',
        string $orientation = 'P'
    ) {
        if (!isset($this->files) || !is_array($this->files)) {
            throw new RuntimeException("No PDFs to merge.");
        }

        switch ($this->library) {
            case self::LIB_FPDF:
                $fpdi = new Fpdi();
                break;
            case self::LIB_TCPDF:
                $fpdi = new FpdiTcpdf();
                break;
            case self::LIB_TFPDF:
                $fpdi = new FpdiTFpdf();
                break;
        }

        // merger operations
        foreach ($this->files as $file) {
            $filename = $file[0];
            $filepages = $file[1];
            $fileorientation = (!is_null($file[2])) ? $file[2] : $orientation;

            $count = $fpdi->setSourceFile($filename);

            //add the pages
            if ($filepages == 'all') {
                for ($i = 1; $i <= $count; $i++) {
                    $template = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($template);

                    $fpdi->AddPage($fileorientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }
            } else {
                foreach ($filepages as $page) {
                    if (!$template = $fpdi->importPage($page)) {
                        throw new RuntimeException(sprintf(
                            "Could not load page '%s' in PDF '%s'. Check that the page exists.",
                            $page,
                            $filename
                        ));
                    }
                    $size = $fpdi->getTemplateSize($template);

                    $fpdi->AddPage($fileorientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($template);
                }
            }
        }

        //output operations
        $mode = $this->switchmode($outputmode);

        if ($mode == 'S') {
            return $fpdi->Output($outputpath, 'S');
        } else {
            if ($fpdi->Output($outputpath, $mode) == '') {
                return true;
            } else {
                throw new RuntimeException("Error outputting PDF to '$outputmode'.");
            }
        }
    }

    /**
     * FPDI uses single characters for specifying the output location.
     * Change our more descriptive string into proper format.
     */
    private function switchmode(string $mode): string
    {
        switch (strtolower($mode)) {
            case 'download':
                return 'D';
                break;
            case 'file':
                return 'F';
                break;
            case 'string':
                return 'S';
                break;
            case 'browser':
            default:
                return 'I';
                break;
        }
    }

    /**
     * Takes our provided pages in the form of 1,3,4,16-50 and creates an array of all pages
     */
    private function rewritepages(string $pages): array
    {
        $pages = str_replace(' ', '', $pages);
        $part = explode(',', $pages);

        $newpages = [];

        //parse hyphens
        foreach ($part as $i) {
            $ind = explode('-', $i);

            if (count($ind) == 2) {
                $start = $ind[0]; //start page
                $end = $ind[1]; //end page

                if ($start > $end) {
                    throw new InvalidArgumentException("Starting page, '$start' is greater than ending page '$end'.");
                }

                //add middle pages
                while ($start <= $end) {
                    $newpages[] = (int) $start;
                    $start++;
                }
            } else {
                $newpages[] = (int) $ind[0];
            }
        }

        return $newpages;
    }
}
