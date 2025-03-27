<?php

namespace Box\Spout\Writer\Common\Creator;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\TestUsingResource;
use PHPUnit\Framework\TestCase;

class WriterFactoryTest extends TestCase
{
    use TestUsingResource;

    public function testCreateFromFileCSV(): void
    {
        $validCsv = $this->getResourcePath('csv_test_create_from_file.csv');
        $writer = WriterFactory::createFromFile($validCsv);
        $this->assertInstanceOf('Box\Spout\Writer\CSV\Writer', $writer);
    }

    public function testCreateFromFileCSVAllCaps(): void
    {
        $validCsv = $this->getResourcePath('csv_test_create_from_file.CSV');
        $writer = WriterFactory::createFromFile($validCsv);
        $this->assertInstanceOf('Box\Spout\Writer\CSV\Writer', $writer);
    }

    public function testCreateFromFileODS(): void
    {
        $validOds = $this->getResourcePath('csv_test_create_from_file.ods');
        $writer = WriterFactory::createFromFile($validOds);
        $this->assertInstanceOf('Box\Spout\Writer\ODS\Writer', $writer);
    }

    public function testCreateFromFileXLSX(): void
    {
        $validXlsx = $this->getResourcePath('csv_test_create_from_file.xlsx');
        $writer = WriterFactory::createFromFile($validXlsx);
        $this->assertInstanceOf('Box\Spout\Writer\XLSX\Writer', $writer);
    }

    public function testCreateWriterShouldThrowWithUnsupportedType(): void
    {
        $this->expectException(UnsupportedTypeException::class);

        WriterFactory::createFromType('unsupportedType');
    }

    public function testCreateFromFileUnsupported(): void
    {
        $this->expectException(UnsupportedTypeException::class);
        $invalid = $this->getResourcePath('test_unsupported_file_type.other');
        WriterFactory::createFromFile($invalid);
    }
}
