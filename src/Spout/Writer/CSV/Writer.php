<?php

namespace Box\Spout\Writer\CSV;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Helper\EncodingHelper;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\WriterAbstract;

/**
 * Class Writer
 * This class provides support to write data to CSV files
 */
class Writer extends WriterAbstract
{
    /** Number of rows to write before flushing */
    public const FLUSH_THRESHOLD = 500;

    /** @var string Content-Type value for the header */
    protected static string $headerContentType = 'text/csv; charset=UTF-8';

    /** @var int */
    protected int $lastWrittenRowIndex = 0;

    /**
     * Sets the field delimiter for the CSV
     *
     * @param string $fieldDelimiter Character that delimits fields
     */
    public function setFieldDelimiter(string $fieldDelimiter): self
    {
        $this->optionsManager->setOption(Options::FIELD_DELIMITER, $fieldDelimiter);

        return $this;
    }

    /**
     * Sets the field enclosure for the CSV
     *
     * @param string $fieldEnclosure Character that enclose fields
     */
    public function setFieldEnclosure(string $fieldEnclosure): self
    {
        $this->optionsManager->setOption(Options::FIELD_ENCLOSURE, $fieldEnclosure);

        return $this;
    }

    /**
     * Set if a BOM has to be added to the file
     */
    public function setShouldAddBOM(bool $shouldAddBOM): self
    {
        $this->optionsManager->setOption(Options::SHOULD_ADD_BOM, (bool) $shouldAddBOM);

        return $this;
    }

    /**
     * Opens the CSV streamer and makes it ready to accept data.
     */
    protected function openWriter(): void
    {
        if ($this->optionsManager->getOption(Options::SHOULD_ADD_BOM)) {
            // Adds UTF-8 BOM for Unicode compatibility
            $this->globalFunctionsHelper->fputs($this->filePointer, EncodingHelper::BOM_UTF8);
        }
    }

    /**
     * Adds a row to the currently opened writer.
     *
     * @param Row $row The row containing cells and styles
     * @throws IOException If unable to write data
     */
    protected function addRowToWriter(Row $row): void
    {
        $fieldDelimiter = $this->optionsManager->getOption(Options::FIELD_DELIMITER);
        $fieldEnclosure = $this->optionsManager->getOption(Options::FIELD_ENCLOSURE);

        $wasWriteSuccessful = $this->globalFunctionsHelper->fputcsv($this->filePointer, $row->getCells(), $fieldDelimiter, $fieldEnclosure);
        if ($wasWriteSuccessful === false) {
            throw new IOException('Unable to write data');
        }

        $this->lastWrittenRowIndex++;
        if ($this->lastWrittenRowIndex % self::FLUSH_THRESHOLD === 0) {
            $this->globalFunctionsHelper->fflush($this->filePointer);
        }
    }

    /**
     * Closes the CSV streamer, preventing any additional writing.
     * If set, sets the headers and redirects output to the browser.
     */
    protected function closeWriter(): void
    {
        $this->lastWrittenRowIndex = 0;
    }
}
