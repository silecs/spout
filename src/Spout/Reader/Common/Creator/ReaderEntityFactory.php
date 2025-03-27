<?php

namespace Box\Spout\Reader\Common\Creator;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderInterface;

/**
 * Class ReaderEntityFactory
 * Factory to create external entities
 */
class ReaderEntityFactory
{
    /**
     * Creates a reader by file extension
     *
     * @param string $path The path to the spreadsheet file. Supported extensions are .csv, .ods and .xlsx
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public static function createReaderFromFile(string $path): ReaderInterface
    {
        return ReaderFactory::createFromFile($path);
    }

    /**
     * This creates an instance of a CSV reader
     */
    public static function createCSVReader(): \Box\Spout\Reader\CSV\Reader
    {
        return ReaderFactory::createFromType(Type::CSV);
    }

    /**
     * This creates an instance of a XLSX reader
     */
    public static function createXLSXReader(): \Box\Spout\Reader\XLSX\Reader
    {
        return ReaderFactory::createFromType(Type::XLSX);
    }

    /**
     * This creates an instance of a ODS reader
     */
    public static function createODSReader(): \Box\Spout\Reader\ODS\Reader
    {
        return ReaderFactory::createFromType(Type::ODS);
    }
}
