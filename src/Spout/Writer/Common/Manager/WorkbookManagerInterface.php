<?php

namespace Box\Spout\Writer\Common\Manager;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Common\Entity\Workbook;
use Box\Spout\Writer\Common\Entity\Worksheet;
use Box\Spout\Writer\Exception\SheetNotFoundException;
use Box\Spout\Writer\Exception\WriterException;

/**
 * Interface WorkbookManagerInterface
 * workbook manager interface, providing the generic interfaces to work with workbook.
 */
interface WorkbookManagerInterface
{
    public function getWorkbook(): ?Workbook;

    /**
     * Creates a new sheet in the workbook and make it the current sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @throws IOException If unable to open the sheet for writing
     * @return Worksheet The created sheet
     */
    public function addNewSheetAndMakeItCurrent(): Worksheet;

    /**
     * @return Worksheet[] All the workbook's sheets
     */
    public function getWorksheets(): array;

    /**
     * Returns the current sheet
     *
     * @return Worksheet The current sheet
     */
    public function getCurrentWorksheet(): Worksheet;

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @param Sheet $sheet The "external" sheet to set as current
     * @throws SheetNotFoundException If the given sheet does not exist in the workbook
     */
    public function setCurrentSheet(Sheet $sheet): void;

    /**
     * Adds a row to the current sheet.
     * If shouldCreateNewSheetsAutomatically option is set to true, it will handle pagination
     * with the creation of new worksheets if one worksheet has reached its maximum capicity.
     *
     * @param Row $row The row to be added
     * @throws IOException If trying to create a new sheet and unable to open the sheet for writing
     * @throws WriterException If unable to write data
     */
    public function addRowToCurrentWorksheet(Row $row): void;

    /**
     * Closes the workbook and all its associated sheets.
     * All the necessary files are written to disk and zipped together to create the final file.
     * All the temporary files are then deleted.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    public function close($finalFilePointer): void;
}
