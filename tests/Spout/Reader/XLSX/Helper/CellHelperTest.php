<?php

namespace Box\Spout\Reader\XLSX\Helper;

use Box\Spout\Common\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CellHelperTest extends TestCase
{
    public static function dataProviderForTestGetColumnIndexFromCellIndex(): array
    {
        return [
            ['A1', 0],
            ['Z3', 25],
            ['AA5', 26],
            ['AB24', 27],
            ['BC5', 54],
            ['BCZ99', 1455],
        ];
    }

    #[DataProvider("dataProviderForTestGetColumnIndexFromCellIndex")]
    public function testGetColumnIndexFromCellIndex(string $cellIndex, int $expectedColumnIndex): void
    {
        $this->assertEquals($expectedColumnIndex, CellHelper::getColumnIndexFromCellIndex($cellIndex));
    }

    public function testGetColumnIndexFromCellIndexShouldThrowIfInvalidCellIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CellHelper::getColumnIndexFromCellIndex('InvalidCellIndex');
    }
}
