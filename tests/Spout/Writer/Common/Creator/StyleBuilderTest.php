<?php

namespace Box\Spout\Writer\Common\Creator\Style;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Writer\Common\Manager\Style\StyleMerger;
use PHPUnit\Framework\TestCase;

class StyleBuilderTest extends TestCase
{

    public function testStyleBuilderShouldApplyBorders(): void
    {
        $border = (new BorderBuilder())
            ->setBorderBottom()
            ->build();
        $style = (new StyleBuilder())->setBorder($border)->build();
        $this->assertTrue($style->shouldApplyBorder());
    }

    public function testStyleBuilderShouldMergeBorders(): void
    {
        $border = (new BorderBuilder())->setBorderBottom(Color::RED, Border::WIDTH_THIN, Border::STYLE_DASHED)->build();

        $baseStyle = (new StyleBuilder())->setBorder($border)->build();
        $currentStyle = (new StyleBuilder())->build();

        $styleMerger = new StyleMerger();
        $mergedStyle = $styleMerger->merge($currentStyle, $baseStyle);

        $this->assertNull($currentStyle->getBorder(), 'Current style has no border');
        $this->assertInstanceOf(Border::class, $baseStyle->getBorder(), 'Base style has a border');
        $this->assertInstanceOf(Border::class, $mergedStyle->getBorder(), 'Merged style has a border');
    }

    public function testStyleBuilderShouldApplyCellAlignment(): void
    {
        $style = (new StyleBuilder())->setCellAlignment(CellAlignment::CENTER)->build();
        $this->assertTrue($style->shouldApplyCellAlignment());
    }

    public function testStyleBuilderShouldThrowOnInvalidCellAlignment(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new StyleBuilder())->setCellAlignment('invalid_cell_alignment')->build();
    }
}
