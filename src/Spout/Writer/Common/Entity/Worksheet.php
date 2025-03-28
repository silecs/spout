<?php

namespace Box\Spout\Writer\Common\Entity;

/**
 * Class Worksheet
 * Entity describing a Worksheet
 */
class Worksheet
{
    /** @var string Path to the XML file that will contain the sheet data */
    private string $filePath;

    /** @var resource|null Pointer to the sheet data file (e.g. xl/worksheets/sheet1.xml) */
    private $filePointer;

    /** @var Sheet The "external" sheet */
    private Sheet $externalSheet;

    /** @var int Maximum number of columns among all the written rows */
    private int $maxNumColumns;

    /** @var int Index of the last written row */
    private int $lastWrittenRowIndex;

    /**
     * Worksheet constructor.
     */
    public function __construct(string $worksheetFilePath, Sheet $externalSheet)
    {
        $this->filePath = $worksheetFilePath;
        $this->filePointer = null;
        $this->externalSheet = $externalSheet;
        $this->maxNumColumns = 0;
        $this->lastWrittenRowIndex = 0;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return ?resource
     */
    public function getFilePointer()
    {
        return $this->filePointer;
    }

    /**
     * @param resource $filePointer
     */
    public function setFilePointer($filePointer): void
    {
        $this->filePointer = $filePointer;
    }

    public function getExternalSheet(): Sheet
    {
        return $this->externalSheet;
    }

    public function getMaxNumColumns(): int
    {
        return $this->maxNumColumns;
    }

    public function setMaxNumColumns(int $maxNumColumns): void
    {
        $this->maxNumColumns = $maxNumColumns;
    }

    public function getLastWrittenRowIndex(): int
    {
        return $this->lastWrittenRowIndex;
    }

    public function setLastWrittenRowIndex(int $lastWrittenRowIndex): void
    {
        $this->lastWrittenRowIndex = $lastWrittenRowIndex;
    }

    /**
     * @return int The ID of the worksheet
     */
    public function getId(): int
    {
        // sheet index is zero-based, while ID is 1-based
        return $this->externalSheet->getIndex() + 1;
    }
}
