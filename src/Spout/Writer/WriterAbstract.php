<?php

namespace Box\Spout\Writer;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\SpoutException;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Common\Manager\OptionsManagerInterface;
use Box\Spout\Writer\Common\Entity\Options;
use Box\Spout\Writer\Exception\WriterAlreadyOpenedException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;

abstract class WriterAbstract implements WriterInterface
{
    /** @var string Path to the output file */
    protected string $outputFilePath = '';

    /** @var resource Pointer to the file/stream we will write to */
    protected $filePointer;

    /** @var bool Indicates whether the writer has been opened or not */
    protected bool $isWriterOpened = false;

    /** @var GlobalFunctionsHelper Helper to work with global functions */
    protected GlobalFunctionsHelper $globalFunctionsHelper;

    protected HelperFactory $helperFactory;

    /** @var OptionsManagerInterface Writer options manager */
    protected OptionsManagerInterface $optionsManager;

    /** @var string Content-Type value for the header - to be defined by child class */
    protected static string $headerContentType;

    public function __construct(
        OptionsManagerInterface $optionsManager,
        GlobalFunctionsHelper $globalFunctionsHelper,
        HelperFactory $helperFactory
    ) {
        $this->optionsManager = $optionsManager;
        $this->globalFunctionsHelper = $globalFunctionsHelper;
        $this->helperFactory = $helperFactory;
    }

    /**
     * Opens the streamer and makes it ready to accept data.
     *
     * @throws IOException If the writer cannot be opened
     */
    abstract protected function openWriter(): void;

    /**
     * Adds a row to the currently opened writer.
     *
     * @param Row $row The row containing cells and styles
     * @throws WriterNotOpenedException If the workbook is not created yet
     * @throws IOException If unable to write data
     */
    abstract protected function addRowToWriter(Row $row): void;

    /**
     * Closes the streamer, preventing any additional writing.
     */
    abstract protected function closeWriter(): void;

    /**
     * {@inheritdoc}
     */
    public function setDefaultRowStyle(Style $defaultStyle): static
    {
        $this->optionsManager->setOption(Options::DEFAULT_ROW_STYLE, $defaultStyle);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function openToFile($outputFilePath): static
    {
        $this->outputFilePath = $outputFilePath;

        $this->filePointer = $this->globalFunctionsHelper->fopen($this->outputFilePath, 'wb+');
        $this->throwIfFilePointerIsNotAvailable();

        $this->openWriter();
        $this->isWriterOpened = true;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function openToBrowser($outputFileName): static
    {
        $this->outputFilePath = $this->globalFunctionsHelper->basename($outputFileName);

        $this->filePointer = $this->globalFunctionsHelper->fopen('php://output', 'w');
        $this->throwIfFilePointerIsNotAvailable();

        // Clear any previous output (otherwise the generated file will be corrupted)
        // @see https://github.com/box/spout/issues/241
        $this->globalFunctionsHelper->ob_end_clean();

        /*
         * Set headers
         *
         * For newer browsers such as Firefox, Chrome, Opera, Safari, etc., they all support and use `filename*`
         * specified by the new standard, even if they do not automatically decode filename; it does not matter;
         * and for older versions of Internet Explorer, they are not recognized `filename*`, will automatically
         * ignore it and use the old `filename` (the only minor flaw is that there must be an English suffix name).
         * In this way, the multi-browser multi-language compatibility problem is perfectly solved, which does not
         * require UA judgment and is more in line with the standard.
         *
         * @see https://github.com/box/spout/issues/745
         * @see https://tools.ietf.org/html/rfc6266
         * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
         */
        $this->globalFunctionsHelper->header('Content-Type: ' . static::$headerContentType);
        $this->globalFunctionsHelper->header(
            'Content-Disposition: attachment; ' .
            'filename="' . rawurlencode($this->outputFilePath) . '"; ' .
            'filename*=UTF-8\'\'' . rawurlencode($this->outputFilePath)
        );

        /*
         * When forcing the download of a file over SSL,IE8 and lower browsers fail
         * if the Cache-Control and Pragma headers are not set.
         *
         * @see http://support.microsoft.com/KB/323308
         * @see https://github.com/liuggio/ExcelBundle/issues/45
         */
        $this->globalFunctionsHelper->header('Cache-Control: max-age=0');
        $this->globalFunctionsHelper->header('Pragma: public');

        $this->openWriter();
        $this->isWriterOpened = true;

        return $this;
    }

    /**
     * Checks if the pointer to the file/stream to write to is available.
     * Will throw an exception if not available.
     *
     * @throws IOException If the pointer is not available
     */
    protected function throwIfFilePointerIsNotAvailable(): void
    {
        if (!is_resource($this->filePointer)) {
            throw new IOException('File pointer has not be opened');
        }
    }

    /**
     * Checks if the writer has already been opened, since some actions must be done before it gets opened.
     * Throws an exception if already opened.
     *
     * @param string $message Error message
     * @throws WriterAlreadyOpenedException If the writer was already opened and must not be.
     */
    protected function throwIfWriterAlreadyOpened(string $message): void
    {
        if ($this->isWriterOpened) {
            throw new WriterAlreadyOpenedException($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRow(Row $row): static
    {
        if ($this->isWriterOpened) {
            try {
                $this->addRowToWriter($row);
            } catch (SpoutException $e) {
                // if an exception occurs while writing data,
                // close the writer and remove all files created so far.
                $this->closeAndAttemptToCleanupAllFiles();

                // re-throw the exception to alert developers of the error
                throw $e;
            }
        } else {
            throw new WriterNotOpenedException('The writer needs to be opened before adding row.');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRows(array $rows): static
    {
        foreach ($rows as $row) {
            if (!$row instanceof Row) {
                $this->closeAndAttemptToCleanupAllFiles();
                throw new InvalidArgumentException('The input should be an array of Row');
            }

            $this->addRow($row);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (!$this->isWriterOpened) {
            return;
        }

        $this->closeWriter();

        if (\is_resource($this->filePointer)) {
            $this->globalFunctionsHelper->fclose($this->filePointer);
        }

        $this->isWriterOpened = false;
    }

    /**
     * Closes the writer and attempts to cleanup all files that were
     * created during the writing process (temp files & final file).
     */
    private function closeAndAttemptToCleanupAllFiles(): void
    {
        // close the writer, which should remove all temp files
        $this->close();

        // remove output file if it was created
        if ($this->globalFunctionsHelper->file_exists((string) $this->outputFilePath)) {
            $outputFolderPath = \dirname($this->outputFilePath);
            $fileSystemHelper = $this->helperFactory->createFileSystemHelper($outputFolderPath);
            $fileSystemHelper->deleteFile($this->outputFilePath);
        }
    }
}
