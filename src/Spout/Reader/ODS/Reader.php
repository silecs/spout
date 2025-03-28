<?php

namespace Box\Spout\Reader\ODS;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\ODS\Creator\InternalEntityFactory;
use Box\Spout\Reader\ReaderAbstract;

/**
 * Class Reader
 * This class provides support to read data from a ODS file
 */
class Reader extends ReaderAbstract
{
    protected ?\ZipArchive $zip = null;

    protected SheetIterator $sheetIterator;

    /**
     * Returns whether stream wrappers are supported
     */
    protected function doesSupportStreamWrapper(): bool
    {
        return false;
    }

    /**
     * Opens the file at the given file path to make it ready to be read.
     *
     * @param  string $filePath Path of the file to be read
     * @throws \Box\Spout\Common\Exception\IOException If the file at the given path or its content cannot be read
     * @throws \Box\Spout\Reader\Exception\NoSheetsFoundException If there are no sheets in the file
     */
    protected function openReader(string $filePath): void
    {
        /** @var InternalEntityFactory $entityFactory */
        $entityFactory = $this->entityFactory;

        $this->zip = $entityFactory->createZipArchive();

        if ($this->zip->open($filePath) === true) {
            /** @var InternalEntityFactory $entityFactory */
            $entityFactory = $this->entityFactory;
            $this->sheetIterator = $entityFactory->createSheetIterator($filePath, $this->optionsManager);
        } else {
            throw new IOException("Could not open $filePath for reading.");
        }
    }

    /**
     * Returns an iterator to iterate over sheets.
     */
    protected function getConcreteSheetIterator(): SheetIterator
    {
        return $this->sheetIterator;
    }

    /**
     * Closes the reader. To be used after reading the file.
     */
    protected function closeReader(): void
    {
        if ($this->zip !== null) {
            $this->zip->close();
        }
    }
}
