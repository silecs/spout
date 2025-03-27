<?php

namespace Spout\Writer\Common\Manager;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Manager\RowManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RowManagerTest extends TestCase
{
    public static function dataProviderForTestIsEmptyRow(): array
    {
        return [
            // cells, expected isEmpty
            [[], true],
            [[new Cell('')], true],
            [[new Cell(''), new Cell('')], true],
            [[new Cell(''), new Cell(''), new Cell('Okay')], false],
        ];
    }

    #[DataProvider("dataProviderForTestIsEmptyRow")]
    public function testIsEmptyRow(array $cells, bool $expectedIsEmpty): void
    {
        $rowManager = new RowManager();

        $row = new Row($cells, null);
        $this->assertEquals($expectedIsEmpty, $rowManager->isEmpty($row));
    }
}
