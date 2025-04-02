<?php

namespace Box\Spout\Writer\ODS;

use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\WriterMultiSheetsAbstract;

/**
 * Class Writer
 * This class provides base support to write data to ODS files
 */
class Writer extends WriterMultiSheetsAbstract
{
    /** @var string Content-Type value for the header */
    protected static string $headerContentType = 'application/vnd.oasis.opendocument.spreadsheet';

    public function addColumnDefaultStyle(Manager\Style\DefaultStyle $default): void
    {
		$worksheet = $this->workbookManager->getCurrentWorksheet();
        $style = $this->workbookManager->addColumnStyle($default);
        $data = '<table:table-column table:style-name="co1" table:default-cell-style-name="ce' . ($style->getId() + 1) . '"/>';
        $wasWriteSuccessful = \fwrite($worksheet->getFilePointer(), $data);
        if ($wasWriteSuccessful === false) {
            throw new IOException("Unable to write data in {$worksheet->getFilePath()}");
        }
    }

    /**
     * Sets a custom temporary folder for creating intermediate files/folders.
     * This must be set before opening the writer.
     *
     * @param string $tempFolder Temporary folder where the files to create the ODS will be stored
     * @throws \Box\Spout\Writer\Exception\WriterAlreadyOpenedException If the writer was already opened
     */
    public function setTempFolder(string $tempFolder): self
    {
        $this->throwIfWriterAlreadyOpened('Writer must be configured before opening it.');

        $this->optionsManager->setOption(Options::TEMP_FOLDER, $tempFolder);

        return $this;
    }
}
