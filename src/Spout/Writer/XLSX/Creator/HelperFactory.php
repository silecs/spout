<?php

namespace Box\Spout\Writer\XLSX\Creator;

use Box\Spout\Common\Helper\Escaper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\Common\Helper\ZipHelper;
use Box\Spout\Writer\XLSX\Helper\FileSystemHelper;

/**
 * Class HelperFactory
 * Factory for helpers needed by the XLSX Writer
 */
class HelperFactory extends \Box\Spout\Common\Creator\HelperFactory
{
    public function createSpecificFileSystemHelper(OptionsManagerInterface $optionsManager, InternalEntityFactory $entityFactory): FileSystemHelper
    {
        $tempFolder = $optionsManager->getOption(Options::TEMP_FOLDER);
        $zipHelper = $this->createZipHelper($entityFactory);
        $escaper = $this->createStringsEscaper();

        return new FileSystemHelper($tempFolder, $zipHelper, $escaper);
    }

    private function createZipHelper(InternalEntityFactory $entityFactory): ZipHelper
    {
        return new ZipHelper($entityFactory);
    }

    public function createStringsEscaper(): Escaper\XLSX
    {
        return new Escaper\XLSX();
    }
}
