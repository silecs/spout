<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\TestUsingResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Performance tests for XLSX Reader
 */
#[Group("perf-tests")]
class ReaderPerfTest extends TestCase
{
    use TestUsingResource;

    public static function dataProviderForTestPerfWhenReading300kRowsXLSX(): array
    {
        return [
            [$shouldUseInlineStrings = true, $expectedMaxExecutionTime = 390], // 6.5 minutes in seconds
            [$shouldUseInlineStrings = false, $expectedMaxExecutionTime = 600], // 10 minutes in seconds
        ];
    }

    /**
     * 300,000 rows (each row containing 3 cells) should be read
     * in less than 6.5 minutes for inline strings, 10 minutes for
     * shared strings and the execution should not require
     * more than 3MB of memory.
     */
    #[DataProvider("dataProviderForTestPerfWhenReading300kRowsXLSX")]
    public function testPerfWhenReading300kRowsXLSX(bool $shouldUseInlineStrings, int $expectedMaxExecutionTime): void
    {
        // getting current memory peak to avoid taking into account the memory used by PHPUnit
        $beforeMemoryPeakUsage = memory_get_peak_usage(true);

        $expectedMaxMemoryPeakUsage = 3 * 1024 * 1024;
        $startTime = time();

        $fileName = ($shouldUseInlineStrings) ? 'xlsx_with_300k_rows_and_inline_strings.xlsx' : 'xlsx_with_300k_rows_and_shared_strings.xlsx';
        $resourcePath = $this->getResourcePath($fileName);

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($resourcePath);

        $numReadRows = 0;

        /** @var Sheet $sheet */
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if (count($row)) {
                    $numReadRows++;
                }
            }
        }

        $reader->close();

        $expectedNumRows = 300000;
        $this->assertEquals($expectedNumRows, $numReadRows, "$expectedNumRows rows should have been read");

        $executionTime = time() - $startTime;
        $this->assertTrue($executionTime < $expectedMaxExecutionTime, "Reading 300,000 rows should take less than $expectedMaxExecutionTime seconds (took $executionTime seconds)");

        $memoryPeakUsage = memory_get_peak_usage(true) - $beforeMemoryPeakUsage;
        $this->assertTrue($memoryPeakUsage < $expectedMaxMemoryPeakUsage, 'Reading 300,000 rows should require less than ' . ($expectedMaxMemoryPeakUsage / 1024 / 1024) . ' MB of memory (required ' . ($memoryPeakUsage / 1024 / 1024) . ' MB)');
    }
}
