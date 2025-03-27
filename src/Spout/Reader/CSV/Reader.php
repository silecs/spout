<?php

namespace Box\Spout\Reader\CSV;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use Box\Spout\Reader\Common\Entity\Options;
use Box\Spout\Reader\CSV\Creator\InternalEntityFactory;
use Box\Spout\Reader\ReaderAbstract;

/**
 * Class Reader
 * This class provides support to read data from a CSV file.
 */
class Reader extends ReaderAbstract
{
    /** @var resource Pointer to the file to be written */
    protected $filePointer;

    /** @var SheetIterator To iterator over the CSV unique "sheet" */
    protected SheetIterator $sheetIterator;

    /** @var string Original value for the "auto_detect_line_endings" INI value */
    protected string $originalAutoDetectLineEndings;

    /** @var bool Whether the code is running with PHP >= 8.1 */
    private bool $isRunningAtLeastPhp81;

    /**
     * @param OptionsManagerInterface $optionsManager
     * @param GlobalFunctionsHelper $globalFunctionsHelper
     * @param InternalEntityFactoryInterface $entityFactory
     */
    public function __construct(
        OptionsManagerInterface $optionsManager,
        GlobalFunctionsHelper $globalFunctionsHelper,
        InternalEntityFactoryInterface $entityFactory
    ) {
        parent::__construct($optionsManager, $globalFunctionsHelper, $entityFactory);
        $this->isRunningAtLeastPhp81 = \version_compare(PHP_VERSION, '8.1.0') >= 0;
    }

    /**
     * Sets the field delimiter for the CSV.
     * Needs to be called before opening the reader.
     */
    public function setFieldDelimiter(string $fieldDelimiter): Reader
    {
        $this->optionsManager->setOption(Options::FIELD_DELIMITER, $fieldDelimiter);

        return $this;
    }

    /**
     * Sets the field enclosure for the CSV.
     * Needs to be called before opening the reader.
     */
    public function setFieldEnclosure(string $fieldEnclosure): Reader
    {
        $this->optionsManager->setOption(Options::FIELD_ENCLOSURE, $fieldEnclosure);

        return $this;
    }

    /**
     * Sets the encoding of the CSV file to be read.
     * Needs to be called before opening the reader.
     */
    public function setEncoding(string $encoding): Reader
    {
        $this->optionsManager->setOption(Options::ENCODING, $encoding);

        return $this;
    }

    /**
     * Returns whether stream wrappers are supported
     */
    protected function doesSupportStreamWrapper(): bool
    {
        return true;
    }

    /**
     * Opens the file at the given path to make it ready to be read.
     * If setEncoding() was not called, it assumes that the file is encoded in UTF-8.
     *
     * @param  string $filePath Path of the CSV file to be read
     * @throws \Box\Spout\Common\Exception\IOException
     */
    protected function openReader(string $filePath): void
    {
        // "auto_detect_line_endings" is deprecated in PHP 8.1
        if (!$this->isRunningAtLeastPhp81) {
            $this->originalAutoDetectLineEndings = \ini_get('auto_detect_line_endings');
            \ini_set('auto_detect_line_endings', '1');
        }

        $this->filePointer = $this->globalFunctionsHelper->fopen($filePath, 'r');
        if (!$this->filePointer) {
            throw new IOException("Could not open file $filePath for reading.");
        }

        /** @var InternalEntityFactory $entityFactory */
        $entityFactory = $this->entityFactory;

        $this->sheetIterator = $entityFactory->createSheetIterator(
            $this->filePointer,
            $this->optionsManager,
            $this->globalFunctionsHelper
        );
    }

    /**
     * Returns an iterator to iterate over sheets.
     *
     * @return SheetIterator To iterate over sheets
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
        if (is_resource($this->filePointer)) {
            $this->globalFunctionsHelper->fclose($this->filePointer);
        }

        // "auto_detect_line_endings" is deprecated in PHP 8.1
        if (!$this->isRunningAtLeastPhp81) {
            \ini_set('auto_detect_line_endings', $this->originalAutoDetectLineEndings);
        }
    }
}
