<?php

namespace Box\Spout\Reader\XLSX\Creator;

use Box\Spout\Reader\Common\Manager\RowManager;
use Box\Spout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactory;
use Box\Spout\Reader\XLSX\Manager\SharedStringsManager;
use Box\Spout\Reader\XLSX\Manager\SheetManager;
use Box\Spout\Reader\XLSX\Manager\StyleManager;
use Box\Spout\Reader\XLSX\Manager\WorkbookRelationshipsManager;

/**
 * Class ManagerFactory
 * Factory to create managers
 */
class ManagerFactory
{
    private HelperFactory $helperFactory;

    private CachingStrategyFactory $cachingStrategyFactory;

    private ?WorkbookRelationshipsManager $cachedWorkbookRelationshipsManager = null;

    /**
     * @param HelperFactory $helperFactory Factory to create helpers
     * @param CachingStrategyFactory $cachingStrategyFactory Factory to create shared strings caching strategies
     */
    public function __construct(HelperFactory $helperFactory, CachingStrategyFactory $cachingStrategyFactory)
    {
        $this->helperFactory = $helperFactory;
        $this->cachingStrategyFactory = $cachingStrategyFactory;
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param string $tempFolder Temporary folder where the temporary files to store shared strings will be stored
     * @param InternalEntityFactory $entityFactory Factory to create entities
     */
    public function createSharedStringsManager(string $filePath, string $tempFolder, InternalEntityFactory $entityFactory): SharedStringsManager
    {
        $workbookRelationshipsManager = $this->createWorkbookRelationshipsManager($filePath, $entityFactory);

        return new SharedStringsManager(
            $filePath,
            $tempFolder,
            $workbookRelationshipsManager,
            $entityFactory,
            $this->helperFactory,
            $this->cachingStrategyFactory
        );
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param InternalEntityFactory $entityFactory Factory to create entities
     */
    private function createWorkbookRelationshipsManager(string $filePath, InternalEntityFactory $entityFactory): WorkbookRelationshipsManager
    {
        if (!isset($this->cachedWorkbookRelationshipsManager)) {
            $this->cachedWorkbookRelationshipsManager = new WorkbookRelationshipsManager($filePath, $entityFactory);
        }

        return $this->cachedWorkbookRelationshipsManager;
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param \Box\Spout\Common\Manager\OptionsManagerInterface $optionsManager Reader's options manager
     * @param SharedStringsManager $sharedStringsManager Manages shared strings
     * @param InternalEntityFactory $entityFactory Factory to create entities
     */
    public function createSheetManager(string $filePath, \Box\Spout\Common\Manager\OptionsManagerInterface $optionsManager, SharedStringsManager $sharedStringsManager, InternalEntityFactory $entityFactory): SheetManager
    {
        $escaper = $this->helperFactory->createStringsEscaper();

        return new SheetManager($filePath, $optionsManager, $sharedStringsManager, $escaper, $entityFactory);
    }

    /**
     * @param string $filePath Path of the XLSX file being read
     * @param InternalEntityFactory $entityFactory Factory to create entities
     */
    public function createStyleManager(string $filePath, InternalEntityFactory $entityFactory): StyleManager
    {
        $workbookRelationshipsManager = $this->createWorkbookRelationshipsManager($filePath, $entityFactory);

        return new StyleManager($filePath, $workbookRelationshipsManager, $entityFactory);
    }

    public function createRowManager(InternalEntityFactory $entityFactory): RowManager
    {
        return new RowManager($entityFactory);
    }
}
