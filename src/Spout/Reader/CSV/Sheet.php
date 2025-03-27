<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Reader\SheetInterface;

/**
 * Class Sheet
 */
class Sheet implements SheetInterface
{
    protected RowIterator $rowIterator;

    public function __construct(RowIterator $rowIterator)
    {
        $this->rowIterator = $rowIterator;
    }

    public function getRowIterator(): RowIterator
    {
        return $this->rowIterator;
    }

    /**
     * @return int Index of the sheet
     */
    public function getIndex(): int
    {
        return 0;
    }

    /**
     * @return string Name of the sheet - empty string since CSV does not support that
     */
    public function getName(): string
    {
        return '';
    }

    /**
     * @return bool Always TRUE as there is only one sheet
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @return bool Always TRUE as the only sheet is always visible
     */
    public function isVisible(): bool
    {
        return true;
    }
}
