<?php

namespace Box\Spout\Writer\CSV;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;
use Box\Spout\TestUsingResource;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\RowCreationHelper;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    use TestUsingResource;
    use RowCreationHelper;

    public function testWriteShouldThrowExceptionIfCannotOpenFileForWriting(): void
    {
        $this->expectException(IOException::class);

        $fileName = 'file_that_wont_be_written.csv';
        $this->createUnwritableFolderIfNeeded();
        $filePath = $this->getGeneratedUnwritableResourcePath($fileName);

        $writer = WriterEntityFactory::createCSVWriter();
        @$writer->openToFile($filePath);
        $writer->addRow($this->createRowFromValues(['csv--11', 'csv--12']));
        $writer->close();
    }

    public function testWriteShouldThrowExceptionIfCallAddRowBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createCSVWriter();
        $writer->addRow($this->createRowFromValues(['csv--11', 'csv--12']));
        $writer->close();
    }

    public function testWriteShouldThrowExceptionIfCallAddRowsBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createCSVWriter();
        $writer->addRow($this->createRowFromValues(['csv--11', 'csv--12']));
        $writer->close();
    }

    public function testAddRowsShouldThrowExceptionIfRowsAreNotArrayOfArrays(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $writer = WriterEntityFactory::createCSVWriter();
        $writer->addRows([['csv--11', 'csv--12']]);
        $writer->close();
    }

    public function testCloseShouldNoopWhenWriterIsNotOpened(): void
    {
        $fileName = 'test_double_close_calls.csv';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createCSVWriter();
        $writer->close(); // This call should not cause any error

        $writer->openToFile($resourcePath);
        $writer->close();
        $writer->close(); // This call should not cause any error
        $this->expectNotToPerformAssertions();
    }

    public function testWriteShouldAddUtf8Bom(): void
    {
        $allRows = $this->createRowsFromValues([
            ['csv--11', 'csv--12'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_utf8_bom.csv');

        $this->assertStringStartsWith(EncodingHelper::BOM_UTF8, $writtenContent, 'The CSV file should contain a UTF-8 BOM');
    }

    public function testWriteShouldNotAddUtf8Bom(): void
    {
        $allRows = $this->createRowsFromValues([
            ['csv--11', 'csv--12'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_no_bom.csv', ',', '"', false);

        $this->assertStringNotContainsString(EncodingHelper::BOM_UTF8, $writtenContent, 'The CSV file should not contain a UTF-8 BOM');
    }

    public function testWriteShouldSupportNullValues(): void
    {
        $allRows = $this->createRowsFromValues([
            ['csv--11', null, 'csv--13'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_null_values.csv');
        $writtenContent = $this->trimWrittenContent($writtenContent);

        $this->assertEquals('csv--11,,csv--13', $writtenContent, 'The null values should be replaced by empty values');
    }

    public function testWriteShouldNotSkipEmptyRows(): void
    {
        $allRows = $this->createRowsFromValues([
            ['csv--11', 'csv--12'],
            [],
            ['csv--31', 'csv--32'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_empty_rows.csv');
        $writtenContent = $this->trimWrittenContent($writtenContent);

        $this->assertEquals("csv--11,csv--12\n\ncsv--31,csv--32", $writtenContent, 'Empty rows should be skipped');
    }

    public function testWriteShouldSupportCustomFieldDelimiter(): void
    {
        $allRows = $this->createRowsFromValues([
            ['csv--11', 'csv--12', 'csv--13'],
            ['csv--21', 'csv--22', 'csv--23'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_pipe_delimiters.csv', '|');
        $writtenContent = $this->trimWrittenContent($writtenContent);

        $this->assertEquals("csv--11|csv--12|csv--13\ncsv--21|csv--22|csv--23", $writtenContent, 'The fields should be delimited with |');
    }

    public function testWriteShouldSupportCustomFieldEnclosure(): void
    {
        $allRows = $this->createRowsFromValues([
            ['This is, a comma', 'csv--12', 'csv--13'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_pound_enclosures.csv', ',', '#');
        $writtenContent = $this->trimWrittenContent($writtenContent);

        $this->assertEquals('#This is, a comma#,csv--12,csv--13', $writtenContent, 'The fields should be enclosed with #');
    }

    public function testWriteShouldSupportedEscapedCharacters(): void
    {
        $allRows = $this->createRowsFromValues([
            ['"csv--11"', 'csv--12\\', 'csv--13\\\\', 'csv--14\\\\\\'],
        ]);
        $writtenContent = $this->writeToCsvFileAndReturnWrittenContent($allRows, 'csv_with_escaped_characters.csv');
        $writtenContent = $this->trimWrittenContent($writtenContent);

        $this->assertEquals('"""csv--11""",csv--12\\,csv--13\\\\,csv--14\\\\\\', $writtenContent, 'The \'"\' and \'\\\' characters should be properly escaped');
    }

    /**
     * @param Row[]  $allRows
     */
    private function writeToCsvFileAndReturnWrittenContent(array $allRows, string $fileName, string $fieldDelimiter = ',', string $fieldEnclosure = '"', bool $shouldAddBOM = true): string
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createCSVWriter();
        $writer->setFieldDelimiter($fieldDelimiter);
        $writer->setFieldEnclosure($fieldEnclosure);
        $writer->setShouldAddBOM($shouldAddBOM);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);
        $writer->close();

        return file_get_contents($resourcePath);
    }

    /**
     * @param string $writtenContent
     * @return string
     */
    private function trimWrittenContent($writtenContent)
    {
        // remove line feeds and UTF-8 BOM
        return trim($writtenContent, PHP_EOL . EncodingHelper::BOM_UTF8);
    }
}
