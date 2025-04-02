<?php

namespace Box\Spout\Writer\ODS;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Reader\Wrapper\XMLReader;
use Box\Spout\TestUsingResource;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Box\Spout\Writer\RowCreationHelper;
use PHPUnit\Framework\TestCase;

class WriterWithStyleTest extends TestCase
{
    use TestUsingResource;
    use RowCreationHelper;

    /** @var Style */
    private $defaultStyle;

    public function setUp() : void
    {
        $this->defaultStyle = (new StyleBuilder())->build();
    }

    public function testAddRowShouldThrowExceptionIfCallAddRowBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->addRow($this->createStyledRowFromValues(['ods--11', 'ods--12'], $this->defaultStyle));
    }

    public function testAddRowShouldThrowExceptionIfCalledBeforeOpeningWriter(): void
    {
        $this->expectException(WriterNotOpenedException::class);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->addRow($this->createStyledRowFromValues(['ods--11', 'ods--12'], $this->defaultStyle));
    }

    public function testAddRowShouldListAllUsedStylesInCreatedContentXmlFile(): void
    {
        $fileName = 'test_add_row_should_list_all_used_fonts.ods';

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontItalic()
            ->setFontUnderline()
            ->setFontStrikethrough()
            ->build();
        $style2 = (new StyleBuilder())
            ->setFontSize(15)
            ->setFontColor(Color::RED)
            ->setFontName('Cambria')
            ->setBackgroundColor(Color::GREEN)
            ->build();

        $dataRows = [
            $this->createStyledRowFromValues(['ods--11', 'ods--12'], $style),
            $this->createStyledRowFromValues(['ods--21', 'ods--22'], $style2),
        ];

        $this->writeToODSFile($dataRows, $fileName);

        $cellStyleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);
        $this->assertGreaterThan(3, count($cellStyleElements), 'There should be at least 3 separate cell styles, including the default one.');

        // Custom styles are at the end, in the order of their creation.
        /** @var \DOMElement $customFont2Element */
        $customFont2Element = array_pop($cellStyleElements);
        /** @var \DOMElement $customFont1Element */
        $customFont1Element = array_pop($cellStyleElements);

        $this->assertFirstChildHasAttributeEquals('bold', $customFont1Element, 'text-properties', 'fo:font-weight');
        $this->assertFirstChildHasAttributeEquals('italic', $customFont1Element, 'text-properties', 'fo:font-style');
        $this->assertFirstChildHasAttributeEquals('solid', $customFont1Element, 'text-properties', 'style:text-underline-style');
        $this->assertFirstChildHasAttributeEquals('solid', $customFont1Element, 'text-properties', 'style:text-line-through-style');

        // Third font should contain data from the second created style
        $this->assertFirstChildHasAttributeEquals('15pt', $customFont2Element, 'text-properties', 'fo:font-size');
        $this->assertFirstChildHasAttributeEquals('#' . Color::RED, $customFont2Element, 'text-properties', 'fo:color');
        $this->assertFirstChildHasAttributeEquals('Cambria', $customFont2Element, 'text-properties', 'style:font-name');
        $this->assertFirstChildHasAttributeEquals('#' . Color::GREEN, $customFont2Element, 'table-cell-properties', 'fo:background-color');
    }

    public function testAddRowShouldWriteDefaultStyleSettings(): void
    {
        $fileName = 'test_add_row_should_write_default_style_settings.ods';
        $dataRow = $this->createStyledRowFromValues(['ods--11', 'ods--12'], $this->defaultStyle);

        $this->writeToODSFile([$dataRow], $fileName);

        $textPropertiesElement = $this->getXmlSectionFromStylesXmlFile($fileName, 'style:text-properties');
        $this->assertEquals(Style::DEFAULT_FONT_SIZE . 'pt', $textPropertiesElement->getAttribute('fo:font-size'));
        $this->assertEquals('#' . Style::DEFAULT_FONT_COLOR, $textPropertiesElement->getAttribute('fo:color'));
        $this->assertEquals(Style::DEFAULT_FONT_NAME, $textPropertiesElement->getAttribute('style:font-name'));
    }

    public function testAddRowShouldApplyStyleToCells(): void
    {
        $fileName = 'test_add_row_should_apply_style_to_cells.ods';

        $style = (new StyleBuilder())->setFontBold()->build();
        $style2 = (new StyleBuilder())->setFontSize(15)->build();
        $dataRows = [
            $this->createStyledRowFromValues(['ods--11'], $style),
            $this->createStyledRowFromValues(['ods--21'], $style2),
            $this->createRowFromValues(['ods--31']),
        ];

        $this->writeToODSFile($dataRows, $fileName);

        $cellDomElements = $this->getCellElementsFromContentXmlFile($fileName);
        $this->assertCount(3, $cellDomElements, 'There should be 3 cells with content');

        $this->assertEquals('ce9', $cellDomElements[0]->getAttribute('table:style-name'));
        $this->assertEquals('ce10', $cellDomElements[1]->getAttribute('table:style-name'));
        $this->assertEquals('', $cellDomElements[2]->getAttribute('table:style-name'));
    }

    public function testAddRowShouldReuseDuplicateStyles(): void
    {
        $fileName = 'test_add_row_should_reuse_duplicate_styles.ods';

        $style = (new StyleBuilder())->setFontBold()->build();
        $dataRows = $this->createStyledRowsFromValues([
            ['ods--11'],
            ['ods--21'],
        ], $style);

        $this->writeToODSFile($dataRows, $fileName);

        $cellDomElements = $this->getCellElementsFromContentXmlFile($fileName);
        $this->assertCount(2, $cellDomElements, 'There should be 2 cells with content');

        $this->assertEquals('ce9', $cellDomElements[0]->getAttribute('table:style-name'));
        $this->assertEquals('ce9', $cellDomElements[1]->getAttribute('table:style-name'));
    }

    public function testAddRowShouldAddWrapTextAlignmentInfoInStylesXmlFileIfSpecified(): void
    {
        $fileName = 'test_add_row_should_add_wrap_text_alignment.ods';

        $style = (new StyleBuilder())->setShouldWrapText()->build();
        $dataRows = $this->createStyledRowsFromValues([
            ['ods--11', 'ods--12'],
        ], $style);

        $this->writeToODSFile($dataRows, $fileName);

        $styleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);
        $this->assertGreaterThan(2, count($styleElements), 'There should be at least 2 styles (default and custom)');

        $customStyleElement = array_pop($styleElements);
        $this->assertFirstChildHasAttributeEquals('wrap', $customStyleElement, 'table-cell-properties', 'fo:wrap-option');
    }

    public function testAddRowShouldApplyWrapTextIfCellContainsNewLine(): void
    {
        $fileName = 'test_add_row_should_apply_wrap_text_if_new_lines.ods';
        $dataRows = $this->createStyledRowsFromValues([
            ["ods--11\nods--11"],
        ], $this->defaultStyle);

        $this->writeToODSFile($dataRows, $fileName);

        $styleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);
        $this->assertGreaterThan(2, count($styleElements), 'There should be at least 2 styles (default and custom)');

        $customStyleElement = array_pop($styleElements);
        $this->assertFirstChildHasAttributeEquals('wrap', $customStyleElement, 'table-cell-properties', 'fo:wrap-option');
    }

    public function testAddRowShouldApplyCellAlignment(): void
    {
        $fileName = 'test_add_row_should_apply_cell_alignment.xlsx';

        $rightAlignedStyle = (new StyleBuilder())->setCellAlignment(CellAlignment::RIGHT)->build();
        $dataRows = $this->createStyledRowsFromValues([['ods--11']], $rightAlignedStyle);

        $this->writeToODSFile($dataRows, $fileName);

        $styleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);
        $this->assertGreaterThan(2, count($styleElements), 'There should be at least 2 styles (default and custom)');

        $customStyleElement = array_pop($styleElements);
        $this->assertFirstChildHasAttributeEquals('end', $customStyleElement, 'paragraph-properties', 'fo:text-align');
    }

    public function testAddRowShouldSupportCellStyling(): void
    {
        $fileName = 'test_add_row_should_support_cell_styling.ods';

        $boldStyle = (new StyleBuilder())->setFontBold()->build();
        $underlineStyle = (new StyleBuilder())->setFontUnderline()->build();

        $dataRow = WriterEntityFactory::createRow([
            WriterEntityFactory::createCell('ods--11', $boldStyle),
            WriterEntityFactory::createCell('ods--12', $underlineStyle),
            WriterEntityFactory::createCell('ods--13', $underlineStyle),
        ]);

        $this->writeToODSFile([$dataRow], $fileName);

        $cellDomElements = $this->getCellElementsFromContentXmlFile($fileName);

        // First row should have 3 styled cells, with cell 2 and 3 sharing the same style
        $this->assertEquals('ce' . ($boldStyle->getId() + 1), $cellDomElements[0]->getAttribute('table:style-name'));
        $this->assertEquals('ce' . ($underlineStyle->getId() + 1), $cellDomElements[1]->getAttribute('table:style-name'));
        $this->assertEquals('ce' . ($underlineStyle->getId() + 1), $cellDomElements[2]->getAttribute('table:style-name'));
    }

    public function testAddBackgroundColor(): void
    {
        $fileName = 'test_default_background_style.ods';

        $style = (new StyleBuilder())->setBackgroundColor(Color::WHITE)->build();
        $dataRows = $this->createStyledRowsFromValues([
            ['defaultBgColor'],
        ], $style);

        $this->writeToODSFile($dataRows, $fileName);

        $styleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);
        $this->assertGreaterThan(2, count($styleElements), 'There should be at least 2 styles (default and custom)');

        $customStyleElement = array_pop($styleElements);
        $this->assertFirstChildHasAttributeEquals('#' . Color::WHITE, $customStyleElement, 'table-cell-properties', 'fo:background-color');
    }

    public function testBorders(): void
    {
        $fileName = 'test_borders.ods';

        $borderBottomGreenThickSolid = (new BorderBuilder())
            ->setBorderBottom(Color::GREEN, Border::WIDTH_THICK, Border::STYLE_SOLID)->build();

        $borderTopRedThinDashed = (new BorderBuilder())
            ->setBorderTop(Color::RED, Border::WIDTH_THIN, Border::STYLE_DASHED)->build();

        $styles =  [
            (new StyleBuilder())->setBorder($borderBottomGreenThickSolid)->build(),
            (new StyleBuilder())->build(),
            (new StyleBuilder())->setBorder($borderTopRedThinDashed)->build(),
        ];

        $dataRows = [
            $this->createStyledRowFromValues(['row-with-border-bottom-green-thick-solid'], $styles[0]),
            $this->createStyledRowFromValues(['row-without-border'], $styles[1]),
            $this->createStyledRowFromValues(['row-with-border-top-red-thin-dashed'], $styles[2]),
        ];

        $this->writeToODSFile($dataRows, $fileName);

        $styleElements = $this->getCellStyleElementsFromContentXmlFile($fileName);

        $this->assertGreaterThan(3, count($styleElements), 'There should be at least 3 styles)');

        // Use reflection for protected members here
        $widthMap = \ReflectionHelper::getStaticValue('Box\Spout\Writer\ODS\Helper\BorderHelper', 'widthMap');
        $styleMap = \ReflectionHelper::getStaticValue('Box\Spout\Writer\ODS\Helper\BorderHelper', 'styleMap');

        $expectedFirst = sprintf(
            '%s %s #%s',
            $widthMap[Border::WIDTH_THICK],
            $styleMap[Border::STYLE_SOLID],
            Color::GREEN
        );

        $actualFirst = $styleElements[count($styleElements) - 2]
            ->getElementsByTagName('table-cell-properties')
            ->item(0)
            ->getAttribute('fo:border-bottom');

        $this->assertEquals($expectedFirst, $actualFirst);

        $expectedThird = sprintf(
            '%s %s #%s',
            $widthMap[Border::WIDTH_THIN],
            $styleMap[Border::STYLE_DASHED],
            Color::RED
        );

        $actualThird = $styleElements[count($styleElements) - 1]
            ->getElementsByTagName('table-cell-properties')
            ->item(0)
            ->getAttribute('fo:border-top');

        $this->assertEquals($expectedThird, $actualThird);
    }

    public function testSetDefaultRowStyle(): void
    {
        $fileName = 'test_set_default_row_style.ods';

        $dataRows = $this->createRowsFromValues([
            ['ods--11'],
        ]);

        $defaultFontSize = 50;
        $defaultStyle = (new StyleBuilder())->setFontSize($defaultFontSize)->build();

        $this->writeToODSFileWithDefaultStyle($dataRows, $fileName, $defaultStyle);

        $textPropertiesElement = $this->getXmlSectionFromStylesXmlFile($fileName, 'style:text-properties');
        $this->assertEquals($defaultFontSize . 'pt', $textPropertiesElement->getAttribute('fo:font-size'));
    }

    /**
     * @param Row[] $allRows
     */
    private function writeToODSFile(array $allRows, string $fileName): Writer
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);
        $writer->close();

        return $writer;
    }

    /**
     * @param Row[] $allRows
     */
    private function writeToODSFileWithDefaultStyle(array $allRows, string $fileName, Style $defaultStyle): Writer
    {
        $this->createGeneratedFolderIfNeeded($fileName);
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $writer = WriterEntityFactory::createODSWriter();
        $writer->setDefaultRowStyle($defaultStyle);

        $writer->openToFile($resourcePath);
        $writer->addRows($allRows);
        $writer->close();

        return $writer;
    }

    /**
     * @return \DOMElement[]
     */
    private function getCellElementsFromContentXmlFile(string $fileName): array
    {
        $cellElements = [];

        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $xmlReader = new XMLReader();
        $xmlReader->openFileInZip($resourcePath, 'content.xml');

        while ($xmlReader->read()) {
            if ($xmlReader->isPositionedOnStartingNode('table:table-cell') && $xmlReader->getAttribute('office:value-type') !== null) {
                /** @var \DOMElement $cellElement */
                $cellElement = $xmlReader->expand();
                $cellElements[] = $cellElement;
            }
        }

        $xmlReader->close();

        return $cellElements;
    }

    /**
     * @return \DOMElement[]
     */
    private function getCellStyleElementsFromContentXmlFile(string $fileName): array
    {
        $cellStyleElements = [];

        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $xmlReader = new XMLReader();
        $xmlReader->openFileInZip($resourcePath, 'content.xml');

        while ($xmlReader->read()) {
            if ($xmlReader->isPositionedOnStartingNode('style:style') && $xmlReader->getAttribute('style:family') === 'table-cell') {
                /** @var \DOMElement $cellStyleElement */
                $cellStyleElement = $xmlReader->expand();
                $cellStyleElements[] = $cellStyleElement;
            }
        }

        $xmlReader->close();

        return $cellStyleElements;
    }

    private function getXmlSectionFromStylesXmlFile(string $fileName, string $section): \DOMElement
    {
        $resourcePath = $this->getGeneratedResourcePath($fileName);

        $xmlReader = new XMLReader();
        $xmlReader->openFileInZip($resourcePath, 'styles.xml');
        $xmlReader->readUntilNodeFound($section);

        /** @var \DOMElement $element */
        $element = $xmlReader->expand();

        return $element;
    }

    private function assertFirstChildHasAttributeEquals(string $expectedValue, \DOMElement $parentElement, string $childTagName, string $attributeName): void
    {
        $this->assertEquals($expectedValue, $parentElement->getElementsByTagName($childTagName)->item(0)->getAttribute($attributeName));
    }
}
