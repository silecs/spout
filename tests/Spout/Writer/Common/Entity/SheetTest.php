<?php

namespace Box\Spout\Writer\Common\Entity;

use Box\Spout\Writer\Common\Manager\SheetManager;
use Box\Spout\Writer\Exception\InvalidSheetNameException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    /** @var SheetManager */
    private $sheetManager;

    public function setUp(): void
    {
        $this->sheetManager = new SheetManager();
    }

    private function createSheet(int $sheetIndex, string $associatedWorkbookId): Sheet
    {
        return new Sheet($sheetIndex, $associatedWorkbookId, $this->sheetManager);
    }

    public function testGetSheetName(): void
    {
        $sheets = [$this->createSheet(0, 'workbookId1'), $this->createSheet(1, 'workbookId1')];

        $this->assertEquals('Sheet1', $sheets[0]->getName(), 'Invalid name for the first sheet');
        $this->assertEquals('Sheet2', $sheets[1]->getName(), 'Invalid name for the second sheet');
    }

    public function testSetSheetNameShouldCreateSheetWithCustomName(): void
    {
        $customSheetName = 'CustomName';
        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        $this->assertEquals($customSheetName, $sheet->getName(), "The sheet name should have been changed to '$customSheetName'");
    }

    public static function dataProviderForInvalidSheetNames(): array
    {
        return [
            [''],
            ['this title exceeds the 31 characters limit'],
            ['Illegal \\'],
            ['Illegal /'],
            ['Illegal ?'],
            ['Illegal *'],
            ['Illegal :'],
            ['Illegal ['],
            ['Illegal ]'],
            ['\'Illegal start'],
            ['Illegal end\''],
        ];
    }

    #[DataProvider("dataProviderForInvalidSheetNames")]
    public function testSetSheetNameShouldThrowOnInvalidName(string $customSheetName): void
    {
        $this->expectException(InvalidSheetNameException::class);

        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);
    }

    public function testSetSheetNameShouldNotThrowWhenSettingSameNameAsCurrentOne(): void
    {
        $customSheetName = 'Sheet name';
        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);
        $sheet->setName($customSheetName);
        $this->expectNotToPerformAssertions();
    }

    public function testSetSheetNameShouldThrowWhenNameIsAlreadyUsed(): void
    {
        $this->expectException(InvalidSheetNameException::class);

        $customSheetName = 'Sheet name';

        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(1, 'workbookId1');
        $sheet->setName($customSheetName);
    }

    public function testSetSheetNameShouldNotThrowWhenSameNameUsedInDifferentWorkbooks(): void
    {
        $customSheetName = 'Sheet name';

        $sheet = $this->createSheet(0, 'workbookId1');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(0, 'workbookId2');
        $sheet->setName($customSheetName);

        $sheet = $this->createSheet(1, 'workbookId3');
        $sheet->setName($customSheetName);
        $this->expectNotToPerformAssertions();
    }
}
