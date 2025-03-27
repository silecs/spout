<?php

namespace Box\Spout\Reader\Common\Manager;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\XLSX\Creator\HelperFactory;
use Box\Spout\Reader\XLSX\Creator\InternalEntityFactory;
use Box\Spout\Reader\XLSX\Creator\ManagerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RowManagerTest extends TestCase
{
    public static function dataProviderForTestFillMissingIndexesWithEmptyCells(): array
    {
        $cell1 = new Cell(1);
        $cell3 = new Cell(3);

        return [
            [[], []],
            [[1 => $cell1, 3 => $cell3], [new Cell(''), $cell1, new Cell(''), $cell3]],
        ];
    }

    /**
     * @param Cell[]|null $rowCells
     * @param Cell[] $expectedFilledCells
     */
    #[DataProvider("dataProviderForTestFillMissingIndexesWithEmptyCells")]
    public function testFillMissingIndexesWithEmptyCells(array $rowCells, array $expectedFilledCells): void
    {
        $rowManager = $this->createRowManager();

        $rowToFill = new Row([], null);
        foreach ($rowCells as $cellIndex => $cell) {
            $rowToFill->setCellAtIndex($cell, $cellIndex);
        }

        $filledRow = $rowManager->fillMissingIndexesWithEmptyCells($rowToFill);
        $this->assertEquals($expectedFilledCells, $filledRow->getCells());
    }

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
        $rowManager = $this->createRowManager();
        $row = new Row($cells, null);

        $this->assertEquals($expectedIsEmpty, $rowManager->isEmpty($row));
    }

    private function createRowManager(): RowManager
    {
        $entityFactory = new InternalEntityFactory(
            $this->createMock(ManagerFactory::class),
            $this->createMock(HelperFactory::class)
        );

        return new RowManager($entityFactory);
    }
}
