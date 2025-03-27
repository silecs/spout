<?php

namespace Box\Spout\Writer\ODS\Creator;

use Box\Spout\Common\Helper\Escaper;
use Box\Spout\Common\Helper\StringHelper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\Common\Helper\ZipHelper;
use Box\Spout\Writer\ODS\Helper\FileSystemHelper;

/**
 * Class HelperFactory
 * Factory for helpers needed by the ODS Writer
 */
class HelperFactory extends \Box\Spout\Common\Creator\HelperFactory
{
    public function createSpecificFileSystemHelper(OptionsManagerInterface $optionsManager, InternalEntityFactory $entityFactory): FileSystemHelper
    {
        $tempFolder = $optionsManager->getOption(Options::TEMP_FOLDER);
        $zipHelper = $this->createZipHelper($entityFactory);

        return new FileSystemHelper($tempFolder, $zipHelper);
    }

    private function createZipHelper(InternalEntityFactory $entityFactory): ZipHelper
    {
        return new ZipHelper($entityFactory);
    }

    public function createStringsEscaper(): Escaper\ODS
    {
        return new Escaper\ODS();
    }

    public function createStringHelper(): StringHelper
    {
        return new StringHelper();
    }
}
