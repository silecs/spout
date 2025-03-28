<?php

namespace Box\Spout\Reader\XLSX;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use Box\Spout\Reader\Common\Entity\Options;
use Box\Spout\Reader\ReaderAbstract;
use Box\Spout\Reader\XLSX\Creator\InternalEntityFactory;
use Box\Spout\Reader\XLSX\Creator\ManagerFactory;

/**
 * Class Reader
 * This class provides support to read data from a XLSX file
 */
class Reader extends ReaderAbstract
{
    protected ManagerFactory $managerFactory;

    protected ?\ZipArchive $zip = null;

    protected ?\Box\Spout\Reader\XLSX\Manager\SharedStringsManager $sharedStringsManager = null;

    /** @var SheetIterator To iterator over the XLSX sheets */
    protected SheetIterator $sheetIterator;

    /**
     * @param OptionsManagerInterface $optionsManager
     * @param GlobalFunctionsHelper $globalFunctionsHelper
     * @param InternalEntityFactoryInterface $entityFactory
     * @param ManagerFactory $managerFactory
     */
    public function __construct(
        OptionsManagerInterface $optionsManager,
        GlobalFunctionsHelper $globalFunctionsHelper,
        InternalEntityFactoryInterface $entityFactory,
        ManagerFactory $managerFactory
    ) {
        parent::__construct($optionsManager, $globalFunctionsHelper, $entityFactory);
        $this->managerFactory = $managerFactory;
    }

    /**
     * @param string $tempFolder Temporary folder where the temporary files will be created
     */
    public function setTempFolder(string $tempFolder): Reader
    {
        $this->optionsManager->setOption(Options::TEMP_FOLDER, $tempFolder);

        return $this;
    }

    /**
     * Returns whether stream wrappers are supported
     */
    protected function doesSupportStreamWrapper(): bool
    {
        return false;
    }

    /**
     * Opens the file at the given file path to make it ready to be read.
     * It also parses the sharedStrings.xml file to get all the shared strings available in memory
     * and fetches all the available sheets.
     *
     * @param  string $filePath Path of the file to be read
     * @throws \Box\Spout\Common\Exception\IOException If the file at the given path or its content cannot be read
     * @throws \Box\Spout\Reader\Exception\NoSheetsFoundException If there are no sheets in the file
     * @return void
     */
    protected function openReader(string $filePath): void
    {
        /** @var InternalEntityFactory $entityFactory */
        $entityFactory = $this->entityFactory;

        $this->zip = $entityFactory->createZipArchive();

        if ($this->zip->open($filePath) === true) {
            $tempFolder = $this->optionsManager->getOption(Options::TEMP_FOLDER);
            $this->sharedStringsManager = $this->managerFactory->createSharedStringsManager($filePath, $tempFolder, $entityFactory);

            if ($this->sharedStringsManager->hasSharedStrings()) {
                // Extracts all the strings from the sheets for easy access in the future
                $this->sharedStringsManager->extractSharedStrings();
            }

            $this->sheetIterator = $entityFactory->createSheetIterator(
                $filePath,
                $this->optionsManager,
                $this->sharedStringsManager
            );
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

        if ($this->sharedStringsManager !== null) {
            $this->sharedStringsManager->cleanup();
        }
    }
}
