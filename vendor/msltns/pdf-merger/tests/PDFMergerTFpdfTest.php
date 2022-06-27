<?php

declare(strict_types=1);

namespace Jmleroux\Tests;

use InvalidArgumentException;
use Jmleroux\PDFMerger\PDFMerger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PDFMergerTFpdfTest extends TestCase
{
    /** @var string */
    private $resultDirectory;
    /** @var string */
    private $samplesDirectory;

    public function setUp(): void
    {
        $this->samplesDirectory = __DIR__ . '/pdfs/';
        $this->resultDirectory = __DIR__ . '/../var/';
    }

    public function testMergePdfAllPages()
    {
        $pdf = new PDFMerger('tfpdf');

        $pdf->addPDF($this->samplesDirectory . 'github_1.pdf')
            ->addPDF($this->samplesDirectory . 'github_2.pdf')
            ->merge('file', $this->resultDirectory . 'test_github_1.pdf');

        $this->verifyFiles('results/tfpdf/result_all_pages.pdf', 'test_github_1.pdf');
    }

    public function testMergePdfSpecificPages()
    {
        $pdf = new PDFMerger('tfpdf');

        $pdf->addPDF($this->samplesDirectory . 'github_home.pdf', '1,2')
            ->addPDF($this->samplesDirectory . 'github_home.pdf', '5,6')
            ->merge('file', $this->resultDirectory . 'test_github_1.pdf');

        $this->verifyFiles('results/tfpdf/result_specific_pages.pdf', 'test_github_1.pdf');
    }

    public function testMergePdfPagesRange()
    {
        $pdf = new PDFMerger('tfpdf');

        $pdf->addPDF($this->samplesDirectory . 'github_home.pdf', '1-3')
            ->addPDF($this->samplesDirectory . 'github_home.pdf', '6,7')
            ->merge('file', $this->resultDirectory . 'test_github_1.pdf');

        $this->verifyFiles('results/tfpdf/result_pages_range.pdf', 'test_github_1.pdf');
    }

    private function verifyFiles(string $original, string $result): void
    {
        $this->assertFileExists($this->resultDirectory . $result);

        $this->assertEquals(
            filesize($this->samplesDirectory . $original),
            filesize($this->resultDirectory . $result)
        );
    }
}
