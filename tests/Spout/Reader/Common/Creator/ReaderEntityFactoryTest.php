<?php

namespace Box\Spout\Reader\Common\Creator;

use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\TestUsingResource;
use PHPUnit\Framework\TestCase;

class ReaderEntityFactoryTest extends TestCase
{
    use TestUsingResource;

    public function testCreateFromFileCSV()
    {
        $validCsv = $this->getResourcePath('csv_test_create_from_file.csv');
        $reader = ReaderEntityFactory::createReaderFromFile($validCsv);
        $this->assertInstanceOf('Box\Spout\Reader\CSV\Reader', $reader);
    }

    public function testCreateFromFileCSVAllCaps()
    {
        $validCsv = $this->getResourcePath('csv_test_create_from_file.CSV');
        $reader = ReaderEntityFactory::createReaderFromFile($validCsv);
        $this->assertInstanceOf('Box\Spout\Reader\CSV\Reader', $reader);
    }

    public function testCreateFromFileODS()
    {
        $validOds = $this->getResourcePath('csv_test_create_from_file.ods');
        $reader = ReaderEntityFactory::createReaderFromFile($validOds);
        $this->assertInstanceOf('Box\Spout\Reader\ODS\Reader', $reader);
    }

    public function testCreateFromFileXLSX()
    {
        $validXlsx = $this->getResourcePath('csv_test_create_from_file.xlsx');
        $reader = ReaderEntityFactory::createReaderFromFile($validXlsx);
        $this->assertInstanceOf('Box\Spout\Reader\XLSX\Reader', $reader);
    }

    public function testCreateFromFileUnsupported()
    {
        $this->expectException(UnsupportedTypeException::class);
        $invalid = $this->getResourcePath('test_unsupported_file_type.other');
        ReaderEntityFactory::createReaderFromFile($invalid);
    }

    public function testCreateFromFileMissingShouldWork()
    {
        $notExistingFile = 'thereisnosuchfile.csv';
        $reader = ReaderEntityFactory::createReaderFromFile($notExistingFile);
        $this->assertInstanceOf('Box\Spout\Reader\CSV\Reader', $reader);
    }
}
