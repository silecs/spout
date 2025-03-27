<?php

namespace Box\Spout\Writer\Common\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CellHelperTest extends TestCase
{
    public static function dataProviderForTestGetColumnLettersFromColumnIndex(): array
    {
        return [
            [0, 'A'],
            [1, 'B'],
            [25, 'Z'],
            [26, 'AA'],
            [28, 'AC'],
        ];
    }

    #[DataProvider("dataProviderForTestGetColumnLettersFromColumnIndex")]
    public function testGetColumnLettersFromColumnIndex(int $columnIndex, string $expectedColumnLetters): void
    {
        $this->assertEquals($expectedColumnLetters, CellHelper::getColumnLettersFromColumnIndex($columnIndex));
    }
}
