<?php

namespace Box\Spout\Reader\CSV\Creator;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use Box\Spout\Reader\CSV\RowIterator;
use Box\Spout\Reader\CSV\Sheet;
use Box\Spout\Reader\CSV\SheetIterator;

/**
 * Class EntityFactory
 * Factory to create entities
 */
class InternalEntityFactory implements InternalEntityFactoryInterface
{
    private HelperFactory $helperFactory;

    public function __construct(HelperFactory $helperFactory)
    {
        $this->helperFactory = $helperFactory;
    }

    /**
     * @param resource $filePointer Pointer to the CSV file to read
     */
    public function createSheetIterator($filePointer, OptionsManagerInterface $optionsManager, GlobalFunctionsHelper $globalFunctionsHelper): SheetIterator
    {
        $rowIterator = $this->createRowIterator($filePointer, $optionsManager, $globalFunctionsHelper);
        $sheet = $this->createSheet($rowIterator);

        return new SheetIterator($sheet);
    }

    private function createSheet(RowIterator $rowIterator): Sheet
    {
        return new Sheet($rowIterator);
    }

    /**
     * @param resource $filePointer Pointer to the CSV file to read
     */
    private function createRowIterator($filePointer, OptionsManagerInterface $optionsManager, GlobalFunctionsHelper $globalFunctionsHelper): RowIterator
    {
        $encodingHelper = $this->helperFactory->createEncodingHelper($globalFunctionsHelper);

        return new RowIterator($filePointer, $optionsManager, $encodingHelper, $this, $globalFunctionsHelper);
    }

    /**
     * @param Cell[] $cells
     */
    public function createRow(array $cells = []): Row
    {
        return new Row($cells, null);
    }

    public function createCell(mixed $cellValue): Cell
    {
        return new Cell($cellValue);
    }

    public function createRowFromArray(array $cellValues = []): Row
    {
        $cells = \array_map(function ($cellValue) {
            return $this->createCell($cellValue);
        }, $cellValues);

        return $this->createRow($cells);
    }
}
