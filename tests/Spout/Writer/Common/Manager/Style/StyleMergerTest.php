<?php

namespace Box\Spout\Writer\Common\Manager\Style;

use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use PHPUnit\Framework\TestCase;

class StyleMergerTest extends TestCase
{
    /** @var StyleMerger */
    private $styleMerger;

    public function setUp(): void
    {
        $this->styleMerger = new StyleMerger();
    }

    public function testMergeWithShouldReturnACopy(): void
    {
        $baseStyle = (new StyleBuilder())->build();
        $currentStyle = (new StyleBuilder())->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertNotSame($mergedStyle, $currentStyle);
    }

    public function testMergeWithShouldMergeSetProperties(): void
    {
        $baseStyle = (new StyleBuilder())
                        ->setFontSize(99)
                        ->setFontBold()
                        ->setFontColor(Color::YELLOW)
                        ->setBackgroundColor(Color::BLUE)
                        ->setFormat('0.00')
                        ->build();
        $currentStyle = (new StyleBuilder())->setFontName('Font')->setFontUnderline()->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertNotEquals(99, $currentStyle->getFontSize());
        $this->assertFalse($currentStyle->isFontBold());
        $this->assertEquals(Style::DEFAULT_FONT_COLOR, $currentStyle->getFontColor());
        $this->assertEquals(null, $currentStyle->getBackgroundColor());

        $this->assertEquals(99, $mergedStyle->getFontSize());
        $this->assertTrue($mergedStyle->isFontBold());
        $this->assertEquals('Font', $mergedStyle->getFontName());
        $this->assertTrue($mergedStyle->isFontUnderline());
        $this->assertEquals(Color::YELLOW, $mergedStyle->getFontColor());
        $this->assertEquals(Color::BLUE, $mergedStyle->getBackgroundColor());
        $this->assertEquals('0.00', $mergedStyle->getFormat());
    }

    public function testMergeWithShouldPreferCurrentStylePropertyIfSetOnCurrentAndOnBase(): void
    {
        $baseStyle = (new StyleBuilder())->setFontSize(10)->build();
        $currentStyle = (new StyleBuilder())->setFontSize(99)->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertEquals(99, $mergedStyle->getFontSize());
    }

    public function testMergeWithShouldPreferCurrentStylePropertyIfSetOnCurrentButNotOnBase(): void
    {
        $baseStyle = (new StyleBuilder())->build();
        $currentStyle = (new StyleBuilder())
                            ->setFontItalic()
                            ->setFontStrikethrough()
                            ->build();

        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertFalse($baseStyle->isFontItalic());
        $this->assertFalse($baseStyle->isFontStrikethrough());

        $this->assertTrue($mergedStyle->isFontItalic());
        $this->assertTrue($mergedStyle->isFontStrikethrough());
    }

    public function testMergeWithShouldPreferBaseStylePropertyIfSetOnBaseButNotOnCurrent(): void
    {
        $baseStyle = (new StyleBuilder())
            ->setFontItalic()
            ->setFontUnderline()
            ->setFontStrikethrough()
            ->setShouldWrapText()
            ->build();
        $currentStyle = (new StyleBuilder())->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertFalse($currentStyle->isFontUnderline());
        $this->assertTrue($mergedStyle->isFontUnderline());

        $this->assertFalse($currentStyle->shouldWrapText());
        $this->assertTrue($mergedStyle->shouldWrapText());
    }

    public function testMergeWithShouldDoNothingIfStylePropertyNotSetOnBaseNorCurrent(): void
    {
        $baseStyle = (new StyleBuilder())->build();
        $currentStyle = (new StyleBuilder())->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertSameStyles($baseStyle, $currentStyle);
        $this->assertSameStyles($currentStyle, $mergedStyle);
    }

    public function testMergeWithShouldDoNothingIfStylePropertyNotSetOnCurrentAndIsDefaultValueOnBase(): void
    {
        $baseStyle = (new StyleBuilder())
            ->setFontName(Style::DEFAULT_FONT_NAME)
            ->setFontSize(Style::DEFAULT_FONT_SIZE)
            ->build();
        $currentStyle = (new StyleBuilder())->build();
        $mergedStyle = $this->styleMerger->merge($currentStyle, $baseStyle);

        $this->assertSameStyles($currentStyle, $mergedStyle);
    }

    private function assertSameStyles(Style $style1, Style $style2): void
    {
        $fakeStyle = (new StyleBuilder())->build();
        $styleRegistry = new StyleRegistry($fakeStyle);

        $this->assertSame($styleRegistry->serialize($style1), $styleRegistry->serialize($style2));
    }
}
