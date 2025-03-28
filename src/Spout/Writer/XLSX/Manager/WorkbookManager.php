<?php

namespace Box\Spout\Writer\XLSX\Manager;

use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Common\Manager\WorkbookManagerAbstract;
use Box\Spout\Writer\XLSX\Helper\FileSystemHelper;

/**
 * Class WorkbookManager
 * XLSX workbook manager, providing the interfaces to work with workbook.
 */
class WorkbookManager extends WorkbookManagerAbstract
{
    /**
     * Maximum number of rows a XLSX sheet can contain
     * @see http://office.microsoft.com/en-us/excel-help/excel-specifications-and-limits-HP010073849.aspx
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
     * @param Sheet $sheet
     * @return string The file path where the data for the given sheet will be stored
     */
    public function getWorksheetFilePath(Sheet $sheet): string
    {
        $helper = $this->fileSystemHelper;
        assert($helper instanceof FileSystemHelper);
        $worksheetFilesFolder = $helper->getXlWorksheetsFolder();

        return $worksheetFilesFolder . '/' . \strtolower($sheet->getName()) . '.xml';
    }

    /**
     * Closes custom objects that are still opened
     */
    protected function closeRemainingObjects(): void
    {
        $worksheetManager = $this->worksheetManager;
        assert($worksheetManager instanceof WorksheetManager);
        $worksheetManager->getSharedStringsManager()->close();
    }

    /**
     * Writes all the necessary files to disk and zip them together to create the final file.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    protected function writeAllFilesToDiskAndZipThem($finalFilePointer): void
    {
        $worksheets = $this->getWorksheets();

        $helper = $this->fileSystemHelper;
        assert($helper instanceof FileSystemHelper);
        $helper
            ->createContentTypesFile($worksheets)
            ->createWorkbookFile($worksheets)
            ->createWorkbookRelsFile($worksheets)
            ->createStylesFile($this->styleManager)
            ->zipRootFolderAndCopyToStream($finalFilePointer);
    }
}
