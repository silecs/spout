<?php

namespace Box\Spout\Writer\ODS;

use Box\Spout\TestUsingResource;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Exception\InvalidSheetNameException;
use Box\Spout\Writer\RowCreationHelper;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    use TestUsingResource;
    use RowCreationHelper;

    public function testGetSheetIndex(): void
    {
        $sheets = $this->writeDataToMulitpleSheetsAndReturnSheets('test_get_sheet_index.ods');

        $this->assertCount(2, $sheets, '2 sheets should have been created');
        $this->assertEquals(0, $sheets[0]->getIndex(), 'The first sheet should be index 0');
        $this->assertEquals(1, $sheets[1]->getIndex(), 'The second sheet should be index 1');
    }

    public function testGetSheetName(): void
    {
        $sheets = $this->writeDataToMulitpleSheetsAndReturnSheets('test_get_sheet_name.ods');

        $this->assertCount(2, $sheets, '2 sheets should have been created');
        $this->assertEquals('Sheet1', $sheets[0]->getName(), 'Invalid name for the first sheet');
        $this->assertEquals('Sheet2', $sheets[1]->getName(), 'Invalid name for the second sheet');
    }

    public function testSetSheetNameShouldCreateSheetWithCustomName(): void
    {
        $fileName = 'test_set_name_should_create_sheet_with_custom_name.ods';
        $customSheetName = 'CustomName';
        $this->writeDataAndReturnSheetWithCustomName($fileName, $customSheetName);

        $this->assertSheetNameEquals($customSheetName, $fileName, "The sheet name should have been changed to '$customSheetName'");
    }

    public function testSetSheetNameShouldThrowWhenNameIsAlreadyUsed(): void
    {
        $this->expectException(InvalidSheetNameException::class);

        $fileName = 'test_set_name_with_non_unique_name.ods';
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($resourcePath);

        $customSheetName = 'Sheet name';

        $sheet = $writer->getCurrentSheet();
        $sheet->setName($customSheetName);

        $writer->addNewSheetAndMakeItCurrent();
        $sheet = $writer->getCurrentSheet();
        $sheet->setName($customSheetName);
    }

    public function testSetSheetVisibilityShouldCreateSheetHidden(): void
    {
        $fileName = 'test_set_visibility_should_create_sheet_hidden.xlsx';
        $this->writeDataToHiddenSheet($fileName);

        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToContentFile = $resourcePath . '#content.xml';
        $xmlContents = file_get_contents('zip://' . $pathToContentFile);

        $this->assertStringContainsString(' table:display="false"', $xmlContents, 'The sheet visibility should have been changed to "hidden"');
    }

    private function writeDataAndReturnSheetWithCustomName(string $fileName, string $sheetName): void
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($resourcePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName($sheetName);

        $writer->addRow($this->createRowFromValues(['ods--11', 'ods--12']));
        $writer->close();
    }

    /**
     * @return Sheet[]
     */
    private function writeDataToMulitpleSheetsAndReturnSheets(string $fileName): array
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($resourcePath);

        $writer->addRow($this->createRowFromValues(['ods--sheet1--11', 'ods--sheet1--12']));
        $writer->addNewSheetAndMakeItCurrent();
        $writer->addRow($this->createRowFromValues(['ods--sheet2--11', 'ods--sheet2--12', 'ods--sheet2--13']));

        $writer->close();

        return $writer->getSheets();
    }

    private function writeDataToHiddenSheet(string $fileName): void
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->openToFile($resourcePath);

        $sheet = $writer->getCurrentSheet();
        $sheet->setIsVisible(false);

        $writer->addRow($this->createRowFromValues(['ods--11', 'ods--12']));
        $writer->close();
    }

    private function assertSheetNameEquals(string $expectedName, string $fileName, string $message = ''): void
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);
        $pathToWorkbookFile = $resourcePath . '#content.xml';
        $xmlContents = file_get_contents('zip://' . $pathToWorkbookFile);

        $this->assertStringContainsString("table:name=\"$expectedName\"", $xmlContents, $message);
    }
}
