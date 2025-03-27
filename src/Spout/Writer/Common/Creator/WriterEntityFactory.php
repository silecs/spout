<?php

namespace Box\Spout\Writer\Common\Creator;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterInterface;

/**
 * Class WriterEntityFactory
 * Factory to create external entities
 */
class WriterEntityFactory
{
    /**
     * This creates an instance of the appropriate writer, given the type of the file to be written
     *
     * @param  string $writerType Type of the writer to instantiate
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public static function createWriter(string $writerType): WriterInterface
    {
        return WriterFactory::createFromType($writerType);
    }

    /**
     * This creates an instance of the appropriate writer, given the extension of the file to be written
     *
     * @param string $path The path to the spreadsheet file. Supported extensions are .csv, .ods and .xlsx
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public static function createWriterFromFile(string $path): WriterInterface
    {
        return WriterFactory::createFromFile($path);
    }

    /**
     * This creates an instance of a CSV writer
     *
     * throws UnsupportedTypeException
     */
    public static function createCSVWriter(): \Box\Spout\Writer\CSV\Writer
    {
        return WriterFactory::createFromType(Type::CSV);
    }

    /**
     * This creates an instance of a XLSX writer
     *
     * @throws UnsupportedTypeException
     */
    public static function createXLSXWriter(): \Box\Spout\Writer\XLSX\Writer
    {
        return WriterFactory::createFromType(Type::XLSX);
    }

    /**
     * This creates an instance of a ODS writer
     *
     * @throws UnsupportedTypeException
     */
    public static function createODSWriter(): \Box\Spout\Writer\ODS\Writer
    {
        return WriterFactory::createFromType(Type::ODS);
    }

    /**
     * @param Cell[] $cells
     */
    public static function createRow(array $cells = [], ?Style $rowStyle = null): Row
    {
        return new Row($cells, $rowStyle);
    }

    public static function createRowFromArray(array $cellValues = [], ?Style $rowStyle = null): Row
    {
        $cells = \array_map(
            fn ($cellValue) => new Cell($cellValue),
            $cellValues
        );

        return new Row($cells, $rowStyle);
    }

    public static function createCell(mixed $cellValue, ?Style $cellStyle = null): Cell
    {
        return new Cell($cellValue, $cellStyle);
    }
}
