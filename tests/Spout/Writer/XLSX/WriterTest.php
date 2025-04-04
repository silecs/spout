<?php

namespace Box\Spout\Writer\XLSX;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\SpoutException;
use Box\Spout\TestUsingResource;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Exception\WriterAlreadyOpenedException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\RowCreationHelper;
use Box\Spout\Writer\XLSX\Manager\WorksheetManager;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    use TestUsingResource;
    use RowCreationHelper;

    public function testAddRowShouldThrowExceptionIfCannotOpenAFileForWriting(): void
    {
        $this->expectException(IOException::class);

        $fileName = 'file_that_wont_be_written.xlsx';
        $this->createUnwritableFolderIfNeeded();
        $filePath = $this->getGeneratedUnwritableResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        @$writer->openToFile($filePath);
    }

    public function testAddRowShouldThrowExceptionIfCallAddRowBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->addRow($this->createRowFromValues(['xlsx--11', 'xlsx--12']));
    }

    public function testAddRowShouldThrowExceptionIfCalledBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->addRows($this->createRowsFromValues([['xlsx--11', 'xlsx--12']]));
    }

    public function testSetTempFolderShouldThrowExceptionIfCalledAfterOpeningWriter(): void
    {
        $this->expectException(WriterAlreadyOpenedException::class);

        $fileName = 'file_that_wont_be_written.xlsx';
        $filePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $writer->setTempFolder('');
    }

    public function testSetShouldUseInlineStringsShouldThrowExceptionIfCalledAfterOpeningWriter(): void
    {
        $this->expectException(WriterAlreadyOpenedException::class);

        $fileName = 'file_that_wont_be_written.xlsx';
        $filePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $writer->setShouldUseInlineStrings(true);
    }

    public function testsetShouldCreateNewSheetsAutomaticallyShouldThrowExceptionIfCalledAfterOpeningWriter(): void
    {
        $this->expectException(WriterAlreadyOpenedException::class);

        $fileName = 'file_that_wont_be_written.xlsx';
        $filePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $writer->setShouldCreateNewSheetsAutomatically(true);
    }

    public function testAddRowShouldThrowExceptionIfUnsupportedDataTypePassedIn(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $fileName = 'test_add_row_should_throw_exception_if_unsupported_data_type_passed_in.xlsx';
        $dataRows = [
            [str_repeat('a', WorksheetManager::MAX_CHARACTERS_PER_CELL + 1)],
        ];

        $this->writeToXLSXFile($dataRows, $fileName);
    }

    public function testAddRowShouldThrowExceptionIfWritingStringExceedingMaxNumberOfCharactersAllowedPerCell(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $fileName = 'test_add_row_should_throw_exception_if_string_exceeds_max_num_chars_allowed_per_cell.xlsx';
        $dataRows = $this->createRowsFromValues([
            [new \stdClass()],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName);
    }

    public function testAddRowShouldCleanupAllFilesIfExceptionIsThrown(): void
    {
        $fileName = 'test_add_row_should_cleanup_all_files_if_exception_thrown.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['wrong'],
            [new \stdClass()],
        ]);

        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $this->recreateTempFolder();
        $tempFolderPath = $this->getTempFolderPath();

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setTempFolder($tempFolderPath);
        $writer->openToFile($resourcePath);

        try {
            $writer->addRows($dataRows);
            $this->fail('Exception should have been thrown');
        } catch (SpoutException $e) {
            $this->assertFileDoesNotExist($fileName, 'Output file should have been deleted');

            $numFiles = iterator_count(new \FilesystemIterator($tempFolderPath, \FilesystemIterator::SKIP_DOTS));
            $this->assertEquals(0, $numFiles, 'All temp files should have been deleted');
        }
    }

    public function testAddNewSheetAndMakeItCurrent(): void
    {
        $fileName = 'test_add_new_sheet_and_make_it_current.xlsx';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($resourcePath);
        $writer->addNewSheetAndMakeItCurrent();
        $writer->close();

        $sheets = $writer->getSheets();
        $this->assertCount(2, $sheets, 'There should be 2 sheets');
        $this->assertEquals($sheets[1], $writer->getCurrentSheet(), 'The current sheet should be the second one.');
    }

    public function testSetCurrentSheet(): void
    {
        $fileName = 'test_set_current_sheet.xlsx';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($resourcePath);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->addNewSheetAndMakeItCurrent();

        $firstSheet = $writer->getSheets()[0];
        $writer->setCurrentSheet($firstSheet);

        $writer->close();

        $this->assertEquals($firstSheet, $writer->getCurrentSheet(), 'The current sheet should be the first one.');
    }

    public function testCloseShouldNoopWhenWriterIsNotOpened(): void
    {
        $fileName = 'test_double_close_calls.xlsx';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->close(); // This call should not cause any error

        $writer->openToFile($resourcePath);
        $writer->close();
        $writer->close(); // This call should not cause any error
        $this->expectNotToPerformAssertions();
    }

    public function testAddRowShouldWriteGivenDataToSheetUsingInlineStrings(): void
    {
        $fileName = 'test_add_row_should_write_given_data_to_sheet_using_inline_strings.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = true);

        foreach ($dataRows as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertInlineDataWasWrittenToSheet($fileName, 1, $cell->getValue());
            }
        }
    }

    public function testAddRowShouldWriteGivenDataToTwoSheetsUsingInlineStrings(): void
    {
        $fileName = 'test_add_row_should_write_given_data_to_two_sheets_using_inline_strings.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ]);

        $numSheets = 2;
        $this->writeToMultipleSheetsInXLSXFile($dataRows, $numSheets, $fileName, $shouldUseInlineStrings = true);

        for ($i = 1; $i <= $numSheets; $i++) {
            foreach ($dataRows as $dataRow) {
                foreach ($dataRow->getCells() as $cell) {
                    $this->assertInlineDataWasWrittenToSheet($fileName, $numSheets, $cell->getValue());
                }
            }
        }
    }

    public function testAddRowShouldWriteGivenDataToSheetUsingSharedStrings(): void
    {
        $fileName = 'test_add_row_should_write_given_data_to_sheet_using_shared_strings.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = false);

        foreach ($dataRows as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertSharedStringWasWritten($fileName, $cell->getValue());
            }
        }
    }

    public function testAddRowShouldWriteGivenDataToTwoSheetsUsingSharedStrings(): void
    {
        $fileName = 'test_add_row_should_write_given_data_to_two_sheets_using_shared_strings.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--11', 'xlsx--12'],
            ['xlsx--21', 'xlsx--22', 'xlsx--23'],
        ]);

        $numSheets = 2;
        $this->writeToMultipleSheetsInXLSXFile($dataRows, $numSheets, $fileName, $shouldUseInlineStrings = false);

        for ($i = 1; $i <= $numSheets; $i++) {
            foreach ($dataRows as $dataRow) {
                foreach ($dataRow->getCells() as $cell) {
                    $this->assertSharedStringWasWritten($fileName, $cell->getValue());
                }
            }
        }
    }

    public function testAddRowShouldSupportAssociativeArrays(): void
    {
        $fileName = 'test_add_row_should_support_associative_arrays.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['foo' => 'xlsx--11', 'bar' => 'xlsx--12'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName);

        foreach ($dataRows as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertInlineDataWasWrittenToSheet($fileName, 1, $cell->getValue());
            }
        }
    }

    public function testAddRowShouldNotWriteEmptyRows(): void
    {
        $fileName = 'test_add_row_should_not_write_empty_rows.xlsx';
        $dataRows = $this->createRowsFromValues([
            [''],
            ['xlsx--21', 'xlsx--22'],
            ['key' => ''],
            [''],
            ['xlsx--51', 'xlsx--52'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName);

        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 'row r="2"');
        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 'row r="5"');
        $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, 'row r="1"');
        $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, 'row r="3"');
        $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, 'row r="4"');
    }

    public function testAddRowShouldSupportMultipleTypesOfData(): void
    {
        $fileName = 'test_add_row_should_support_multiple_types_of_data.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--11', true, '', 0, 10.2, null],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = false);

        $this->assertSharedStringWasWritten($fileName, 'xlsx--11');
        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 1); // true is converted to 1
        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 0);
        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 10.2);
    }

    public function testAddRowShouldSupportCellInError()
    {
        $fileName = 'test_add_row_should_support_cell_in_error.xlsx';

        $cell = WriterEntityFactory::createCell('#DIV/0');
        $cell->setType(Cell::TYPE_ERROR);

        $row = WriterEntityFactory::createRow([$cell]);

        $this->writeToXLSXFile([$row], $fileName);

        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 't="e"><v>#DIV/0</v>');
    }

    public function testAddRowShouldSupportFloatValuesInDifferentLocale(): void
    {
        $previousLocale = \setlocale(LC_ALL, 0);
        $valueToWrite = 1234.5; // needs to be defined before changing the locale as PHP8 would expect 1234,5

        try {
            // Pick a supported locale whose decimal point is a comma.
            // Installed locales differ from one system to another, so we can't pick
            // a given locale.
            $supportedLocales = explode("\n", shell_exec('locale -a'));
            $foundCommaLocale = false;
            foreach ($supportedLocales as $supportedLocale) {
                \setlocale(LC_ALL, $supportedLocale);
                if (\localeconv()['decimal_point'] === ',') {
                    $foundCommaLocale = true;
                    break;
                }
            }

            if (!$foundCommaLocale) {
                $this->markTestSkipped('No locale with comma decimal separator');
            }

            $this->assertEquals(',', \localeconv()['decimal_point']);

            $fileName = 'test_add_row_should_support_float_values_in_different_locale.xlsx';
            $dataRows = $this->createRowsFromValues([
                [$valueToWrite],
            ]);

            $this->writeToXLSXFile($dataRows, $fileName, $shouldUseInlineStrings = false);

            $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, '1234,5');
            $this->assertInlineDataWasWrittenToSheet($fileName, 1, '1234.5');
        } finally {
            // reset locale
            \setlocale(LC_ALL, $previousLocale);
        }
    }

    public function testAddRowShouldWriteGivenDataToTheCorrectSheet(): void
    {
        $fileName = 'test_add_row_should_write_given_data_to_the_correct_sheet.xlsx';
        $dataRowsSheet1 = $this->createRowsFromValues([
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
        ]);
        $dataRowsSheet2 = $this->createRowsFromValues([
            ['xlsx--sheet2--11', 'xlsx--sheet2--12'],
            ['xlsx--sheet2--21', 'xlsx--sheet2--22', 'xlsx--sheet2--23'],
        ]);
        $dataRowsSheet1Again = $this->createRowsFromValues([
            ['xlsx--sheet1--31', 'xlsx--sheet1--32'],
            ['xlsx--sheet1--41', 'xlsx--sheet1--42', 'xlsx--sheet1--43'],
        ]);

        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setShouldUseInlineStrings(true);

        $writer->openToFile($resourcePath);

        $writer->addRows($dataRowsSheet1);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->addRows($dataRowsSheet2);

        $firstSheet = $writer->getSheets()[0];
        $writer->setCurrentSheet($firstSheet);

        $writer->addRows($dataRowsSheet1Again);

        $writer->close();

        foreach ($dataRowsSheet1 as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertInlineDataWasWrittenToSheet($fileName, 1, $cell->getValue(), 'Data should have been written in Sheet 1');
            }
        }
        foreach ($dataRowsSheet2 as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertInlineDataWasWrittenToSheet($fileName, 2, $cell->getValue(), 'Data should have been written in Sheet 2');
            }
        }
        foreach ($dataRowsSheet1Again as $dataRow) {
            foreach ($dataRow->getCells() as $cell) {
                $this->assertInlineDataWasWrittenToSheet($fileName, 1, $cell->getValue(), 'Data should have been written in Sheet 1');
            }
        }
    }

    public function testAddRowShouldAutomaticallyCreateNewSheetsIfMaxRowsReachedAndOptionTurnedOn(): void
    {
        $fileName = 'test_add_row_should_automatically_create_new_sheets_if_max_rows_reached_and_option_turned_on.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
            ['xlsx--sheet2--11', 'xlsx--sheet2--12'], // this should be written in a new sheet
        ]);

        // set the maxRowsPerSheet limit to 2
        \ReflectionHelper::setStaticValue('\Box\Spout\Writer\XLSX\Manager\WorkbookManager', 'maxRowsPerWorksheet', 2);

        $writer = $this->writeToXLSXFile($dataRows, $fileName, true, $shouldCreateSheetsAutomatically = true);
        $this->assertCount(2, $writer->getSheets(), '2 sheets should have been created.');

        $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, 'xlsx--sheet2--11');
        $this->assertInlineDataWasWrittenToSheet($fileName, 2, 'xlsx--sheet2--11');

        \ReflectionHelper::reset();
    }

    public function testAddRowShouldNotCreateNewSheetsIfMaxRowsReachedAndOptionTurnedOff(): void
    {
        $fileName = 'test_add_row_should_not_create_new_sheets_if_max_rows_reached_and_option_turned_off.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['xlsx--sheet1--11', 'xlsx--sheet1--12'],
            ['xlsx--sheet1--21', 'xlsx--sheet1--22', 'xlsx--sheet1--23'],
            ['xlsx--sheet1--31', 'xlsx--sheet1--32'], // this should NOT be written in a new sheet
        ]);

        // set the maxRowsPerSheet limit to 2
        \ReflectionHelper::setStaticValue('\Box\Spout\Writer\XLSX\Manager\WorkbookManager', 'maxRowsPerWorksheet', 2);

        $writer = $this->writeToXLSXFile($dataRows, $fileName, true, $shouldCreateSheetsAutomatically = false);
        $this->assertCount(1, $writer->getSheets(), 'Only 1 sheet should have been created.');

        $this->assertInlineDataWasNotWrittenToSheet($fileName, 1, 'xlsx--sheet1--31');

        \ReflectionHelper::reset();
    }

    public function testAddRowShouldEscapeHtmlSpecialCharacters(): void
    {
        $fileName = 'test_add_row_should_escape_html_special_characters.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['I\'m in "great" mood', 'This <must> be escaped & tested'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName);

        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 'I&#039;m in &quot;great&quot; mood', 'Quotes should be escaped');
        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 'This &lt;must&gt; be escaped &amp; tested', '<, > and & should be escaped');
    }

    public function testAddRowShouldEscapeControlCharacters(): void
    {
        $fileName = 'test_add_row_should_escape_control_characters.xlsx';
        $dataRows = $this->createRowsFromValues([
            ['control ' . chr(21) . ' character'],
        ]);

        $this->writeToXLSXFile($dataRows, $fileName);

        $this->assertInlineDataWasWrittenToSheet($fileName, 1, 'control _x0015_ character');
    }

    public function testGeneratedFileShouldHaveTheCorrectMimeType(): void
    {
        if (!function_exists('finfo')) {
            $this->markTestSkipped('finfo is not available on this system (possibly running on Windows where the DLL needs to be added explicitly to the php.ini)');
        }

        $fileName = 'test_mime_type.xlsx';
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $dataRows = $this->createRowsFromValues([['foo']]);

        $this->writeToXLSXFile($dataRows, $fileName);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $finfo->file($resourcePath));
    }

    /**
     * @param Row[] $allRows
     */
    private function writeToXLSXFile(array $allRows, string $fileName, bool $shouldUseInlineStrings = true, bool $shouldCreateSheetsAutomatically = true): Writer
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setShouldUseInlineStrings($shouldUseInlineStrings);
        $writer->setShouldCreateNewSheetsAutomatically($shouldCreateSheetsAutomatically);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);
        $writer->close();

        return $writer;
    }

    /**
     * @param Row[] $allRows
     */
    private function writeToMultipleSheetsInXLSXFile(array $allRows, int $numSheets, string $fileName, bool $shouldUseInlineStrings = true, bool $shouldCreateSheetsAutomatically = true): Writer
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->setShouldUseInlineStrings($shouldUseInlineStrings);
        $writer->setShouldCreateNewSheetsAutomatically($shouldCreateSheetsAutomatically);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);

        for ($i = 1; $i < $numSheets; $i++) {
            $writer->addNewSheetAndMakeItCurrent();
            $writer->addRows($allRows);
        }

        $writer->close();

        return $writer;
    }

    private function assertInlineDataWasWrittenToSheet(string $fileName, int $sheetIndex, mixed $inlineData, string $message = ''): void
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSheetFile = $resourcePath . '#xl/worksheets/sheet' . $sheetIndex . '.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSheetFile);

        $this->assertStringContainsString((string) $inlineData, $xmlContents, $message);
    }

    private function assertInlineDataWasNotWrittenToSheet(string $fileName, int $sheetIndex, mixed $inlineData, string $message = ''): void
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSheetFile = $resourcePath . '#xl/worksheets/sheet' . $sheetIndex . '.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSheetFile);

        $this->assertStringNotContainsString((string) $inlineData, $xmlContents, $message);
    }

    private function assertSharedStringWasWritten(string $fileName, string $sharedString, string $message = ''): void
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToSharedStringsFile = $resourcePath . '#xl/sharedStrings.xml';
        $xmlContents = file_get_contents('zip://' . $pathToSharedStringsFile);

        $this->assertStringContainsString($sharedString, $xmlContents, $message);
    }
}
