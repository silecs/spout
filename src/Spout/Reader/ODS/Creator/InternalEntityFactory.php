<?php

namespace Box\Spout\Reader\ODS\Creator;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use Box\Spout\Reader\Common\Entity\Options;
use Box\Spout\Reader\Common\XMLProcessor;
use Box\Spout\Reader\ODS\RowIterator;
use Box\Spout\Reader\ODS\Sheet;
use Box\Spout\Reader\ODS\SheetIterator;
use Box\Spout\Reader\Wrapper\XMLReader;

/**
 * Class EntityFactory
 * Factory to create entities
 */
class InternalEntityFactory implements InternalEntityFactoryInterface
{
    private HelperFactory $helperFactory;

    private ManagerFactory $managerFactory;

    public function __construct(HelperFactory $helperFactory, ManagerFactory $managerFactory)
    {
        $this->helperFactory = $helperFactory;
        $this->managerFactory = $managerFactory;
    }

    /**
     * @param string $filePath Path of the file to be read
     * @param OptionsManagerInterface $optionsManager Reader's options manager
     * @return SheetIterator
     */
    public function createSheetIterator(string $filePath, OptionsManagerInterface$optionsManager): SheetIterator
    {
        $escaper = $this->helperFactory->createStringsEscaper();
        $settingsHelper = $this->helperFactory->createSettingsHelper($this);

        return new SheetIterator($filePath, $optionsManager, $escaper, $settingsHelper, $this);
    }

    /**
     * @param XMLReader $xmlReader XML Reader
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     * @param OptionsManagerInterface $optionsManager Reader's options manager
     */
    public function createSheet(XMLReader $xmlReader, int $sheetIndex, string $sheetName, bool $isSheetActive, bool $isSheetVisible, OptionsManagerInterface $optionsManager): Sheet
    {
        $rowIterator = $this->createRowIterator($xmlReader, $optionsManager);

        return new Sheet($rowIterator, $sheetIndex, $sheetName, $isSheetActive, $isSheetVisible);
    }

    private function createRowIterator(XMLReader $xmlReader, OptionsManagerInterface $optionsManager): RowIterator
    {
        $shouldFormatDates = $optionsManager->getOption(Options::SHOULD_FORMAT_DATES);
        $cellValueFormatter = $this->helperFactory->createCellValueFormatter($shouldFormatDates);
        $xmlProcessor = $this->createXMLProcessor($xmlReader);
        $rowManager = $this->managerFactory->createRowManager($this);

        return new RowIterator($xmlReader, $optionsManager, $cellValueFormatter, $xmlProcessor, $rowManager, $this);
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

    public function createXMLReader(): XMLReader
    {
        return new XMLReader();
    }

    private function createXMLProcessor(XMLReader $xmlReader): XMLProcessor
    {
        return new XMLProcessor($xmlReader);
    }

    public function createZipArchive(): \ZipArchive
    {
        return new \ZipArchive();
    }
}
