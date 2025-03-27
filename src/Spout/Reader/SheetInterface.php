<?php

namespace Box\Spout\Reader;

/**
 * Interface SheetInterface
 */
interface SheetInterface
{
    /**
     * @return IteratorInterface Iterator to iterate over the sheet's rows.
     */
    public function getRowIterator(): IteratorInterface;

    /**
     * @return int Index of the sheet
     */
    public function getIndex(): int;

    /**
     * @return string Name of the sheet
     */
    public function getName(): string;

    /**
     * @return bool Whether the sheet was defined as active
     */
    public function isActive(): bool;

    /**
     * @return bool Whether the sheet is visible
     */
    public function isVisible(): bool;
}
