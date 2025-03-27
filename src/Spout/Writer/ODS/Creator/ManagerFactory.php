<?php

namespace Box\Spout\Writer\ODS\Creator;

use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Creator\ManagerFactoryInterface;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\Common\Manager\SheetManager;
use Box\Spout\Writer\Common\Manager\Style\StyleMerger;
use Box\Spout\Writer\ODS\Manager\Style\StyleManager;
use Box\Spout\Writer\ODS\Manager\Style\StyleRegistry;
use Box\Spout\Writer\ODS\Manager\WorkbookManager;
use Box\Spout\Writer\ODS\Manager\WorksheetManager;

/**
 * Class ManagerFactory
 * Factory for managers needed by the ODS Writer
 */
class ManagerFactory implements ManagerFactoryInterface
{
    protected InternalEntityFactory $entityFactory;

    protected HelperFactory $helperFactory;

    public function __construct(InternalEntityFactory $entityFactory, HelperFactory $helperFactory)
    {
        $this->entityFactory = $entityFactory;
        $this->helperFactory = $helperFactory;
    }

    public function createWorkbookManager(OptionsManagerInterface $optionsManager): WorkbookManager
    {
        $workbook = $this->entityFactory->createWorkbook();

        $fileSystemHelper = $this->helperFactory->createSpecificFileSystemHelper($optionsManager, $this->entityFactory);
        $fileSystemHelper->createBaseFilesAndFolders();

        $styleMerger = $this->createStyleMerger();
        $styleManager = $this->createStyleManager($optionsManager);
        $worksheetManager = $this->createWorksheetManager($styleManager, $styleMerger);

        return new WorkbookManager(
            $workbook,
            $optionsManager,
            $worksheetManager,
            $styleManager,
            $styleMerger,
            $fileSystemHelper,
            $this->entityFactory,
            $this
        );
    }

    private function createWorksheetManager(StyleManager $styleManager, StyleMerger $styleMerger): WorksheetManager
    {
        $stringsEscaper = $this->helperFactory->createStringsEscaper();

        return new WorksheetManager($styleManager, $styleMerger, $stringsEscaper);
    }

    public function createSheetManager(): SheetManager
    {
        return new SheetManager();
    }

    private function createStyleManager(OptionsManagerInterface $optionsManager): StyleManager
    {
        $styleRegistry = $this->createStyleRegistry($optionsManager);

        return new StyleManager($styleRegistry);
    }

    private function createStyleRegistry(OptionsManagerInterface $optionsManager): StyleRegistry
    {
        $defaultRowStyle = $optionsManager->getOption(Options::DEFAULT_ROW_STYLE);

        return new StyleRegistry($defaultRowStyle);
    }

    private function createStyleMerger(): StyleMerger
    {
        return new StyleMerger();
    }
}
