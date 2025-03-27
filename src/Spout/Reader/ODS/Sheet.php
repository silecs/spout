<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Reader\SheetInterface;

/**
 * Class Sheet
 * Represents a sheet within a ODS file
 */
class Sheet implements SheetInterface
{
    protected RowIterator $rowIterator;

    /** @var int ID of the sheet */
    protected int $id;

    /** @var int Index of the sheet, based on order in the workbook (zero-based) */
    protected int $index;

    /** @var string Name of the sheet */
    protected string $name;

    /** @var bool Whether the sheet was the active one */
    protected bool $isActive;

    /** @var bool Whether the sheet is visible */
    protected bool $isVisible;

    /**
     * @param RowIterator $rowIterator The corresponding row iterator
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     */
    public function __construct(RowIterator $rowIterator, int $sheetIndex, string $sheetName, bool $isSheetActive, bool $isSheetVisible)
    {
        $this->rowIterator = $rowIterator;
        $this->index = $sheetIndex;
        $this->name = $sheetName;
        $this->isActive = $isSheetActive;
        $this->isVisible = $isSheetVisible;
    }

    public function getRowIterator(): RowIterator
    {
        return $this->rowIterator;
    }

    /**
     * @return int Index of the sheet, based on order in the workbook (zero-based)
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool Whether the sheet was defined as active
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool Whether the sheet is visible
     */
    public function isVisible(): bool
    {
        return $this->isVisible;
    }
}
