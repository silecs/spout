<?php

namespace Box\Spout\Writer\Common\Manager;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Creator\ManagerFactoryInterface;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Common\Entity\Workbook;
use Box\Spout\Writer\Common\Entity\Worksheet;
use Box\Spout\Writer\Common\Helper\FileSystemWithRootFolderHelperInterface;
use Box\Spout\Writer\Common\Manager\Style\StyleManagerInterface;
use Box\Spout\Writer\Common\Manager\Style\StyleMerger;
use Box\Spout\Writer\Exception\SheetNotFoundException;
use Box\Spout\Writer\Exception\WriterException;

/**
 * Class WorkbookManagerAbstract
 * Abstract workbook manager, providing the generic interfaces to work with workbook.
 */
abstract class WorkbookManagerAbstract implements WorkbookManagerInterface
{
    /** @var ?Workbook The workbook to manage */
    protected ?Workbook $workbook;

    protected OptionsManagerInterface $optionsManager;

    protected WorksheetManagerInterface $worksheetManager;

    protected StyleManagerInterface $styleManager;

    /** @var StyleMerger Helper to merge styles */
    protected StyleMerger $styleMerger;

    /** @var FileSystemWithRootFolderHelperInterface Helper to perform file system operations */
    protected FileSystemWithRootFolderHelperInterface $fileSystemHelper;

    /** @var InternalEntityFactory Factory to create entities */
    protected InternalEntityFactory $entityFactory;

    /** @var ManagerFactoryInterface Factory to create managers */
    protected ManagerFactoryInterface $managerFactory;

    /** @var Worksheet The worksheet where data will be written to */
    protected Worksheet $currentWorksheet;

    public function __construct(
        Workbook $workbook,
        OptionsManagerInterface $optionsManager,
        WorksheetManagerInterface $worksheetManager,
        StyleManagerInterface $styleManager,
        StyleMerger $styleMerger,
        FileSystemWithRootFolderHelperInterface $fileSystemHelper,
        InternalEntityFactory $entityFactory,
        ManagerFactoryInterface $managerFactory
    ) {
        $this->workbook = $workbook;
        $this->optionsManager = $optionsManager;
        $this->worksheetManager = $worksheetManager;
        $this->styleManager = $styleManager;
        $this->styleMerger = $styleMerger;
        $this->fileSystemHelper = $fileSystemHelper;
        $this->entityFactory = $entityFactory;
        $this->managerFactory = $managerFactory;
    }

    /**
     * @return int Maximum number of rows/columns a sheet can contain
     */
    abstract protected function getMaxRowsPerWorksheet(): int;

    /**
     * @param Sheet $sheet
     * @return string The file path where the data for the given sheet will be stored
     */
    abstract protected function getWorksheetFilePath(Sheet $sheet): string;

    public function getWorkbook(): ?Workbook
    {
        return $this->workbook;
    }

    /**
     * Creates a new sheet in the workbook and make it the current sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @throws IOException If unable to open the sheet for writing
     * @return Worksheet The created sheet
     */
    public function addNewSheetAndMakeItCurrent(): Worksheet
    {
        $worksheet = $this->addNewSheet();
        $this->setCurrentWorksheet($worksheet);

        return $worksheet;
    }

    /**
     * Creates a new sheet in the workbook. The current sheet remains unchanged.
     *
     * @throws \Box\Spout\Common\Exception\IOException If unable to open the sheet for writing
     * @return Worksheet The created sheet
     */
    private function addNewSheet(): Worksheet
    {
        $worksheets = $this->getWorksheets();

        $newSheetIndex = \count($worksheets);
        $sheetManager = $this->managerFactory->createSheetManager();
        $sheet = $this->entityFactory->createSheet($newSheetIndex, $this->workbook->getInternalId(), $sheetManager);

        $worksheetFilePath = $this->getWorksheetFilePath($sheet);
        $worksheet = $this->entityFactory->createWorksheet($worksheetFilePath, $sheet);

        $this->worksheetManager->startSheet($worksheet);

        $worksheets[] = $worksheet;
        $this->workbook->setWorksheets($worksheets);

        return $worksheet;
    }

    /**
     * @return Worksheet[] All the workbook's sheets
     */
    public function getWorksheets(): array
    {
        return $this->workbook->getWorksheets();
    }

    /**
     * Returns the current sheet
     *
     * @return Worksheet The current sheet
     */
    public function getCurrentWorksheet(): Worksheet
    {
        return $this->currentWorksheet;
    }

    /**
     * Sets the given sheet as the current one. New data will be written to this sheet.
     * The writing will resume where it stopped (i.e. data won't be truncated).
     *
     * @param Sheet $sheet The "external" sheet to set as current
     * @throws SheetNotFoundException If the given sheet does not exist in the workbook
     * @return void
     */
    public function setCurrentSheet(Sheet $sheet): void
    {
        $worksheet = $this->getWorksheetFromExternalSheet($sheet);
        if ($worksheet !== null) {
            $this->currentWorksheet = $worksheet;
        } else {
            throw new SheetNotFoundException('The given sheet does not exist in the workbook.');
        }
    }

    /**
     * @param Worksheet $worksheet
     * @return void
     */
    private function setCurrentWorksheet($worksheet): void
    {
        $this->currentWorksheet = $worksheet;
    }

    /**
     * Returns the worksheet associated to the given external sheet.
     *
     * @param Sheet $sheet
     * @return Worksheet|null The worksheet associated to the given external sheet or null if not found.
     */
    private function getWorksheetFromExternalSheet(Sheet $sheet): ?Worksheet
    {
        $worksheetFound = null;

        foreach ($this->getWorksheets() as $worksheet) {
            if ($worksheet->getExternalSheet() === $sheet) {
                $worksheetFound = $worksheet;
                break;
            }
        }

        return $worksheetFound;
    }

    /**
     * Adds a row to the current sheet.
     * If shouldCreateNewSheetsAutomatically option is set to true, it will handle pagination
     * with the creation of new worksheets if one worksheet has reached its maximum capicity.
     *
     * @param Row $row The row to be added
     * @throws IOException If trying to create a new sheet and unable to open the sheet for writing
     * @throws WriterException If unable to write data
     */
    public function addRowToCurrentWorksheet(Row $row): void
    {
        $currentWorksheet = $this->getCurrentWorksheet();
        $hasReachedMaxRows = $this->hasCurrentWorksheetReachedMaxRows();

        // if we reached the maximum number of rows for the current sheet...
        if ($hasReachedMaxRows) {
            // ... continue writing in a new sheet if option set
            if ($this->optionsManager->getOption(Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY)) {
                $currentWorksheet = $this->addNewSheetAndMakeItCurrent();

                $this->addRowToWorksheet($currentWorksheet, $row);
            } else {
                // otherwise, do nothing as the data won't be written anyways
            }
        } else {
            $this->addRowToWorksheet($currentWorksheet, $row);
        }
    }

    /**
     * @return bool Whether the current worksheet has reached the maximum number of rows per sheet.
     */
    private function hasCurrentWorksheetReachedMaxRows(): bool
    {
        $currentWorksheet = $this->getCurrentWorksheet();

        return ($currentWorksheet->getLastWrittenRowIndex() >= $this->getMaxRowsPerWorksheet());
    }

    /**
     * Adds a row to the given sheet.
     *
     * @param Worksheet $worksheet Worksheet to write the row to
     * @param Row $row The row to be added
     * @throws WriterException If unable to write data
     */
    private function addRowToWorksheet(Worksheet $worksheet, Row $row): void
    {
        $this->applyDefaultRowStyle($row);
        $this->worksheetManager->addRow($worksheet, $row);

        // update max num columns for the worksheet
        $currentMaxNumColumns = $worksheet->getMaxNumColumns();
        $cellsCount = $row->getNumCells();
        $worksheet->setMaxNumColumns(\max($currentMaxNumColumns, $cellsCount));
    }

    private function applyDefaultRowStyle(Row $row): void
    {
        $defaultRowStyle = $this->optionsManager->getOption(Options::DEFAULT_ROW_STYLE);

        if ($defaultRowStyle !== null) {
            $mergedStyle = $this->styleMerger->merge($row->getStyle(), $defaultRowStyle);
            $row->setStyle($mergedStyle);
        }
    }

    /**
     * Closes the workbook and all its associated sheets.
     * All the necessary files are written to disk and zipped together to create the final file.
     * All the temporary files are then deleted.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    public function close($finalFilePointer): void
    {
        $this->closeAllWorksheets();
        $this->closeRemainingObjects();
        $this->writeAllFilesToDiskAndZipThem($finalFilePointer);
        $this->cleanupTempFolder();
    }

    /**
     * Closes custom objects that are still opened
     */
    protected function closeRemainingObjects(): void
    {
        // do nothing by default
    }

    /**
     * Writes all the necessary files to disk and zip them together to create the final file.
     *
     * @param resource $finalFilePointer Pointer to the spreadsheet that will be created
     */
    abstract protected function writeAllFilesToDiskAndZipThem($finalFilePointer): void;

    /**
     * Closes all workbook's associated sheets.
     */
    private function closeAllWorksheets(): void
    {
        $worksheets = $this->getWorksheets();

        foreach ($worksheets as $worksheet) {
            $this->worksheetManager->close($worksheet);
        }
    }

    /**
     * Deletes the root folder created in the temp folder and all its contents.
     */
    protected function cleanupTempFolder(): void
    {
        $rootFolder = $this->fileSystemHelper->getRootFolder();
        $this->fileSystemHelper->deleteFolderRecursively($rootFolder);
    }
}
