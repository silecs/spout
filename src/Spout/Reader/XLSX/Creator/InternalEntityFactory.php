<?php

namespace Box\Spout\Reader\XLSX\Creator;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Reader\Common\Creator\InternalEntityFactoryInterface;
use Box\Spout\Reader\Common\Entity\Options;
use Box\Spout\Reader\Common\XMLProcessor;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\Reader\XLSX\Manager\SharedStringsManager;
use Box\Spout\Reader\XLSX\RowIterator;
use Box\Spout\Reader\XLSX\Sheet;
use Box\Spout\Reader\XLSX\SheetIterator;

/**
 * Class InternalEntityFactory
 * Factory to create entities
 */
class InternalEntityFactory implements InternalEntityFactoryInterface
{
    private HelperFactory $helperFactory;

    private ManagerFactory $managerFactory;

    public function __construct(ManagerFactory $managerFactory, HelperFactory $helperFactory)
    {
        $this->managerFactory = $managerFactory;
        $this->helperFactory = $helperFactory;
    }

    /**
     * @param string $filePath Path of the file to be read
     * @param OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     */
    public function createSheetIterator(string $filePath, OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager): SheetIterator
    {
        $sheetManager = $this->managerFactory->createSheetManager(
            $filePath,
            $optionsManager,
            $sharedStringsManager,
            $this
        );

        return new SheetIterator($sheetManager);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @param int $sheetIndex Index of the sheet, based on order in the workbook (zero-based)
     * @param string $sheetName Name of the sheet
     * @param bool $isSheetActive Whether the sheet was defined as active
     * @param bool $isSheetVisible Whether the sheet is visible
     * @param \Box\Spout\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @return Sheet
     */
    public function createSheet(
        string $filePath,
        string $sheetDataXMLFilePath,
        int $sheetIndex,
        string $sheetName,
        bool $isSheetActive,
        bool $isSheetVisible,
        OptionsManagerInterface $optionsManager,
        SharedStringsManager $sharedStringsManager
    ) {
        $rowIterator = $this->createRowIterator($filePath, $sheetDataXMLFilePath, $optionsManager, $sharedStringsManager);

        return new Sheet($rowIterator, $sheetIndex, $sheetName, $isSheetActive, $isSheetVisible);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $sheetDataXMLFilePath Path of the sheet data XML file as in [Content_Types].xml
     * @param OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @return RowIterator
     */
    private function createRowIterator(string $filePath, string $sheetDataXMLFilePath, OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager): RowIterator
    {
        $xmlReader = $this->createXMLReader();
        $xmlProcessor = $this->createXMLProcessor($xmlReader);

        $styleManager = $this->managerFactory->createStyleManager($filePath, $this);
        $rowManager = $this->managerFactory->createRowManager($this);
        $shouldFormatDates = $optionsManager->getOption(Options::SHOULD_FORMAT_DATES);
        $shouldUse1904Dates = $optionsManager->getOption(Options::SHOULD_USE_1904_DATES);

        $cellValueFormatter = $this->helperFactory->createCellValueFormatter(
            $sharedStringsManager,
            $styleManager,
            $shouldFormatDates,
            $shouldUse1904Dates
        );

        $shouldPreserveEmptyRows = $optionsManager->getOption(Options::SHOULD_PRESERVE_EMPTY_ROWS);

        return new RowIterator(
            $filePath,
            $sheetDataXMLFilePath,
            $shouldPreserveEmptyRows,
            $xmlReader,
            $xmlProcessor,
            $cellValueFormatter,
            $rowManager,
            $this
        );
    }

    /**
     * @param Cell[] $cells
     */
    public function createRow(array $cells = []): Row
    {
        return new Row($cells, null);
    }

    /**
     * @param mixed $cellValue
     */
    public function createCell($cellValue): Cell
    {
        return new Cell($cellValue);
    }

    public function createZipArchive(): \ZipArchive
    {
        return new \ZipArchive();
    }

    public function createXMLReader(): XMLReader
    {
        return new XMLReader();
    }

    public function createXMLProcessor(XMLReader $xmlReader): XMLProcessor
    {
        return new XMLProcessor($xmlReader);
    }
}
