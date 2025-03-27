<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\CSV\Creator\InternalEntityFactory;
use Box\Spout\Reader\CSV\Manager\OptionsManager;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\TestUsingResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    use TestUsingResource;

    public function testOpenShouldThrowExceptionIfFileDoesNotExist(): void
    {
        $this->expectException(IOException::class);

        $this->createCSVReader()->open('/path/to/fake/file.csv');
    }

    public function testOpenShouldThrowExceptionIfTryingToReadBeforeOpeningReader(): void
    {
        $this->expectException(ReaderNotOpenedException::class);

        $this->createCSVReader()->getSheetIterator();
    }

    public function testOpenShouldThrowExceptionIfFileNotReadable(): void
    {
        $this->expectException(IOException::class);

        /** @var \Box\Spout\Common\Helper\GlobalFunctionsHelper|\PHPUnit\Framework\MockObject\MockObject $helperStub */
        $helperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\GlobalFunctionsHelper')
                        ->onlyMethods(['is_readable'])
                        ->getMock();
        $helperStub->method('is_readable')->willReturn(false);

        $resourcePath = $this->getResourcePath('csv_standard.csv');

        $reader = $this->createCSVReader(null, $helperStub);
        $reader->open($resourcePath);
    }

    public function testOpenShouldThrowExceptionIfCannotOpenFile(): void
    {
        $this->expectException(IOException::class);

        /** @var \Box\Spout\Common\Helper\GlobalFunctionsHelper|\PHPUnit\Framework\MockObject\MockObject $helperStub */
        $helperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\GlobalFunctionsHelper')
                        ->onlyMethods(['fopen'])
                        ->getMock();
        $helperStub->method('fopen')->willReturn(false);

        $resourcePath = $this->getResourcePath('csv_standard.csv');

        $reader = $this->createCSVReader(null, $helperStub);
        $reader->open($resourcePath);
    }

    public function testReadStandardCSV(): void
    {
        $allRows = $this->getAllRowsForFile('csv_standard.csv');

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
            ['csv--31', 'csv--32', 'csv--33'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadShouldNotStopAtCommaIfEnclosed(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_comma_enclosed.csv');
        $this->assertEquals('This is, a comma', $allRows[0][0]);
    }

    public function testReadShouldKeepEmptyCells(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_empty_cells.csv');

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', '', 'csv--23'],
            ['csv--31', 'csv--32', ''],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadShouldSkipEmptyLinesIfShouldPreserveEmptyRowsNotSet(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_multiple_empty_lines.csv');

        $expectedRows = [
            // skipped row here
            ['csv--21', 'csv--22', 'csv--23'],
            // skipped row here
            ['csv--41', 'csv--42', 'csv--43'],
            // skipped row here
            // last row empty
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadShouldReturnEmptyLinesIfShouldPreserveEmptyRowsSet(): void
    {
        $allRows = $this->getAllRowsForFile(
            'csv_with_multiple_empty_lines.csv',
            ',',
            '"',
            EncodingHelper::ENCODING_UTF8,
            $shouldPreserveEmptyRows = true
        );

        $expectedRows = [
            [''],
            ['csv--21', 'csv--22', 'csv--23'],
            [''],
            ['csv--41', 'csv--42', 'csv--43'],
            [''],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public static function dataProviderForTestReadShouldReadEmptyFile(): array
    {
        return [
            ['csv_empty.csv'],
            ['csv_all_lines_empty.csv'],
        ];
    }

    #[DataProvider("dataProviderForTestReadShouldReadEmptyFile")]
    public function testReadShouldReadEmptyFile(string $fileName): void
    {
        $allRows = $this->getAllRowsForFile($fileName);
        $this->assertEquals([], $allRows);
    }

    public function testReadShouldHaveTheRightNumberOfCells(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_different_cells_number.csv');

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22'],
            ['csv--31'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadShouldSupportCustomFieldDelimiter(): void
    {
        $allRows = $this->getAllRowsForFile('csv_delimited_with_pipes.csv', '|');

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
            ['csv--31', 'csv--32', 'csv--33'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadShouldSupportCustomFieldEnclosure(): void
    {
        $allRows = $this->getAllRowsForFile('csv_text_enclosed_with_pound.csv', ',', '#');
        $this->assertEquals('This is, a comma', $allRows[0][0]);
    }

    public function testReadShouldSupportEscapedCharacters(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_escaped_characters.csv');

        $expectedRow = ['"csv--11"', 'csv--12\\', 'csv--13\\\\', 'csv--14\\\\\\'];
        $this->assertEquals([$expectedRow], $allRows);
    }

    public function testReadShouldNotTruncateLineBreak(): void
    {
        $allRows = $this->getAllRowsForFile('csv_with_line_breaks.csv');

        $newLine = PHP_EOL; // to support both Unix and Windows
        $this->assertEquals("This is,{$newLine}a comma", $allRows[0][0]);
    }

    public static function dataProviderForTestReadShouldSkipBom(): array
    {
        return [
            ['csv_with_utf8_bom.csv', EncodingHelper::ENCODING_UTF8],
            ['csv_with_utf16le_bom.csv', EncodingHelper::ENCODING_UTF16_LE],
            ['csv_with_utf16be_bom.csv', EncodingHelper::ENCODING_UTF16_BE],
            ['csv_with_utf32le_bom.csv', EncodingHelper::ENCODING_UTF32_LE],
            ['csv_with_utf32be_bom.csv', EncodingHelper::ENCODING_UTF32_BE],
        ];
    }

    #[DataProvider("dataProviderForTestReadShouldSkipBom")]
    public function testReadShouldSkipBom(string $fileName, string $fileEncoding): void
    {
        $allRows = $this->getAllRowsForFile($fileName, ',', '"', $fileEncoding);

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
            ['csv--31', 'csv--32', 'csv--33'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public static function dataProviderForTestReadShouldSupportNonUTF8FilesWithoutBOMs(): array
    {
        $shouldUseIconv = true;
        $shouldNotUseIconv = false;

        return [
            ['csv_with_encoding_utf16le_no_bom.csv', EncodingHelper::ENCODING_UTF16_LE, $shouldUseIconv],
            ['csv_with_encoding_utf16le_no_bom.csv', EncodingHelper::ENCODING_UTF16_LE, $shouldNotUseIconv],
            ['csv_with_encoding_cp1252.csv', 'CP1252', $shouldUseIconv],
            ['csv_with_encoding_cp1252.csv', 'CP1252', $shouldNotUseIconv],
        ];
    }

    #[DataProvider("dataProviderForTestReadShouldSupportNonUTF8FilesWithoutBOMs")]
    public function testReadShouldSupportNonUTF8FilesWithoutBOMs(string $fileName, string $fileEncoding, bool $shouldUseIconv): void
    {
        $allRows = [];
        $resourcePath = $this->getResourcePath($fileName);

        /** @var \Box\Spout\Common\Helper\GlobalFunctionsHelper|\PHPUnit\Framework\MockObject\MockObject $helperStub */
        $helperStub = $this->getMockBuilder('\Box\Spout\Common\Helper\GlobalFunctionsHelper')
                        ->onlyMethods(['function_exists'])
                        ->getMock();

        $returnValueMap = [
            ['iconv', $shouldUseIconv],
            ['mb_convert_encoding', true],
        ];
        $helperStub->method('function_exists')->willReturnMap($returnValueMap);

        /** @var \Box\Spout\Reader\CSV\Reader $reader */
        $reader = $this->createCSVReader(null, $helperStub);
        $reader
            ->setEncoding($fileEncoding)
            ->open($resourcePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $allRows[] = $row->toArray();
            }
        }

        $reader->close();

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
            ['csv--31', 'csv--32', 'csv--33'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    public function testReadMultipleTimesShouldRewindReader(): void
    {
        $allRows = [];
        $resourcePath = $this->getResourcePath('csv_standard.csv');

        $reader = $this->createCSVReader();
        $reader->open($resourcePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            // do nothing
        }

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $allRows[] = $row->toArray();
                break;
            }

            foreach ($sheet->getRowIterator() as $row) {
                $allRows[] = $row->toArray();
                break;
            }
        }

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $allRows[] = $row->toArray();
                break;
            }
        }

        $reader->close();

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--11', 'csv--12', 'csv--13'],
        ];
        $this->assertEquals($expectedRows, $allRows);
    }

    /**
     * https://github.com/box/spout/issues/184
     */
    public function testReadShouldInludeRowsWithZerosOnly(): void
    {
        $allRows = $this->getAllRowsForFile('sheet_with_zeros_in_row.csv');

        $expectedRows = [
            ['A', 'B', 'C'],
            ['1', '2', '3'],
            ['0', '0', '0'],
        ];
        $this->assertEquals($expectedRows, $allRows, 'There should be only 3 rows, because zeros (0) are valid values');
    }

    /**
     * https://github.com/box/spout/issues/184
     */
    public function testReadShouldCreateOutputEmptyCellPreserved(): void
    {
        $allRows = $this->getAllRowsForFile('sheet_with_empty_cells.csv');

        $expectedRows = [
            ['A', 'B', 'C'],
            ['0', '', ''],
            ['1', '1', ''],
        ];
        $this->assertEquals($expectedRows, $allRows, 'There should be 3 rows, with equal length');
    }

    /**
     * https://github.com/box/spout/issues/195
     */
    public function testReaderShouldNotTrimCellValues(): void
    {
        $allRows = $this->getAllRowsForFile('sheet_with_untrimmed_strings.csv');

        $newLine = PHP_EOL; // to support both Unix and Windows
        $expectedRows = [
            ['A'],
            [' A '],
            ["$newLine\tA$newLine\t"],
        ];

        $this->assertEquals($expectedRows, $allRows, 'Cell values should not be trimmed');
    }

    public function testReadCustomStreamWrapper(): void
    {
        $allRows = [];
        $resourcePath = 'spout://csv_standard';

        // register stream wrapper
        stream_wrapper_register('spout', SpoutTestStream::CLASS_NAME);

        /** @var \Box\Spout\Reader\CSV\Reader $reader */
        $reader = $this->createCSVReader();
        $reader->open($resourcePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $allRows[] = $row->toArray();
            }
        }

        $reader->close();

        $expectedRows = [
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
            ['csv--31', 'csv--32', 'csv--33'],
        ];
        $this->assertEquals($expectedRows, $allRows);

        // cleanup
        stream_wrapper_unregister('spout');
    }

    public function testReadWithUnsupportedCustomStreamWrapper(): void
    {
        $this->expectException(IOException::class);

        /** @var \Box\Spout\Reader\CSV\Reader $reader */
        $reader = $this->createCSVReader();
        $reader->open('unsupported://foobar');
    }

    /**
     * @param \Box\Spout\Common\Helper\GlobalFunctionsHelper|null $optionsManager
     * @param \Box\Spout\Common\Manager\OptionsManagerInterface|null $globalFunctionsHelper
     */
    private function createCSVReader($optionsManager = null, $globalFunctionsHelper = null): ReaderInterface
    {
        $optionsManager = $optionsManager ?: new OptionsManager();
        $globalFunctionsHelper = $globalFunctionsHelper ?: new GlobalFunctionsHelper();
        $entityFactory = new InternalEntityFactory(new HelperFactory());

        return new Reader($optionsManager, $globalFunctionsHelper, $entityFactory);
    }

    /**
     * @return array All the read rows the given file
     */
    private function getAllRowsForFile(
        string $fileName,
        string $fieldDelimiter = ',',
        string $fieldEnclosure = '"',
        string $encoding = EncodingHelper::ENCODING_UTF8,
        bool $shouldPreserveEmptyRows = false
    ) : array
    {
        $allRows = [];
        $resourcePath = $this->getResourcePath($fileName);

        /** @var \Box\Spout\Reader\CSV\Reader $reader */
        $reader = $this->createCSVReader();
        $reader
            ->setFieldDelimiter($fieldDelimiter)
            ->setFieldEnclosure($fieldEnclosure)
            ->setEncoding($encoding)
            ->setShouldPreserveEmptyRows($shouldPreserveEmptyRows)
            ->open($resourcePath);

        foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $allRows[] = $row->toArray();
            }
        }

        $reader->close();

        return $allRows;
    }
}
