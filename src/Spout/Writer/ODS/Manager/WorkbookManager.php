<?php

namespace Box\Spout\Writer\ODS\Manager;

use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Common\Manager\WorkbookManagerAbstract;

/**
 * Class WorkbookManager
 * ODS workbook manager, providing the interfaces to work with workbook.
 */
class WorkbookManager extends WorkbookManagerAbstract
{
    /**
     * Maximum number of rows a ODS sheet can contain
     * @see https://ask.libreoffice.org/en/question/8631/upper-limit-to-number-of-rows-in-calc/
     */
    protected static int $maxRowsPerWorksheet = 1048576;

    /**
     * @return int Maximum number of rows/columns a sheet can contain
     */
    protected function getMaxRowsPerWorksheet(): int
    {
        return self::$maxRowsPerWorksheet;
    }

    /**
     * @return string The file path where the data for the given sheet will be stored
     */
    public function getWorksheetFilePath(Sheet $sheet): string
    {
        $helper = $this->fileSystemHelper;
        assert($helper instanceof \Box\Spout\Writer\ODS\Helper\FileSystemHelper);
        $sheetsContentTempFolder = $helper->getSheetsContentTempFolder();

        return $sheetsContentTempFolder . '/sheet' . $sheet->getIndex() . '.xml';
    }

    /**
     * Writes all the necessary files to disk and zip them together to create the final file.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    protected function writeAllFilesToDiskAndZipThem($finalFilePointer): void
    {
        $worksheets = $this->getWorksheets();
        $numWorksheets = \count($worksheets);

        $helper = $this->fileSystemHelper;
        assert($helper instanceof \Box\Spout\Writer\ODS\Helper\FileSystemHelper);
        $helper
            ->createContentFile($this->worksheetManager, $this->styleManager, $worksheets)
            ->deleteWorksheetTempFolder()
            ->createStylesFile($this->styleManager, $numWorksheets)
            ->zipRootFolderAndCopyToStream($finalFilePointer);
    }
}
