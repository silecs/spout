<?php

namespace tests\benchmarks;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\ReaderAbstract;

class ReaderBench
{
    public function benchReadOds()
    {
        $reader = ReaderEntityFactory::createODSReader();
        $cells = self::readFile($reader, 'ods/static_400-6.ods');
    }

    public function benchReadOdsWithDates()
    {
        $reader = ReaderEntityFactory::createODSReader();
        $cells = self::readFile($reader, 'ods/sheet_with_dates_and_times.ods');
    }

    public function benchReadXlsx()
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $cells = self::readFile($reader, 'xlsx/sheet_with_lots_of_shared_strings.xlsx');
        $cells += self::readFile($reader, 'xlsx/sheet_with_strict_ooxml.xlsx');
    }

    public function benchReadXlsxWithDates()
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $cells = self::readFile($reader, 'xlsx/sheet_with_dates_and_times.xlsx');
    }

    public function benchReadXlsxWithFormulas()
    {
        $reader = ReaderEntityFactory::createXLSXReader();
        $cells = self::readFile($reader, 'xlsx/sheet_with_formulas.xlsx');
    }

    private static function readFile(ReaderAbstract $reader, string $file) : int
    {
        $reader->open(dirname(__DIR__) . "/resources/$file");
        $cells = 0;
        foreach ($reader->getSheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                foreach ($row->getCells() as $cell) {
                    if ($cell->getValue()) {
                        $cells++;
                    }
                }
            }
        }

        return $cells;
    }
}
