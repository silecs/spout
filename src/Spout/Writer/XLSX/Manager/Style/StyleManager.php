<?php

namespace Box\Spout\Writer\XLSX\Manager\Style;

use Box\Spout\Common\Entity\Style\BorderPart;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\XLSX\Helper\BorderHelper;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager extends \Box\Spout\Writer\Common\Manager\Style\StyleManager
{
    /**
     * For empty cells, we can specify a style or not. If no style are specified,
     * then the software default will be applied. But sometimes, it may be useful
     * to override this default style, for instance if the cell should have a
     * background color different than the default one or some borders
     * (fonts property don't really matter here).
     *
     * @return bool Whether the cell should define a custom style
     */
    public function shouldApplyStyleOnEmptyCell(int $styleId): bool
    {
        $associatedFillId = $this->getStyleRegistry()->getFillIdForStyleId($styleId);
        $hasStyleCustomFill = ($associatedFillId !== null && $associatedFillId !== 0);

        $associatedBorderId = $this->getStyleRegistry()->getBorderIdForStyleId($styleId);
        $hasStyleCustomBorders = ($associatedBorderId !== null && $associatedBorderId !== 0);

        $associatedFormatId = $this->getStyleRegistry()->getFormatIdForStyleId($styleId);
        $hasStyleCustomFormats = ($associatedFormatId !== null && $associatedFormatId !== 0);

        return ($hasStyleCustomFill || $hasStyleCustomBorders || $hasStyleCustomFormats);
    }

    /**
     * Returns the content of the "styles.xml" file, given a list of styles.
     */
    public function getStylesXMLFileContent(): string
    {
        $content = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
EOD;

        $content .= $this->getFormatsSectionContent();
        $content .= $this->getFontsSectionContent();
        $content .= $this->getFillsSectionContent();
        $content .= $this->getBordersSectionContent();
        $content .= $this->getCellStyleXfsSectionContent();
        $content .= $this->getCellXfsSectionContent();
        $content .= $this->getCellStylesSectionContent();

        $content .= <<<'EOD'
</styleSheet>
EOD;

        return $content;
    }

    /**
     * Return the local implementation of StyleRegistryInterface, with more methods than the interface.
     */
    protected function getStyleRegistry(): StyleRegistry
    {
        assert($this->styleRegistry instanceof StyleRegistry);
        return $this->styleRegistry;
    }

    /**
     * Returns the content of the "<numFmts>" section.
     */
    protected function getFormatsSectionContent(): string
    {
        $tags = [];
        $registeredFormats = $this->getStyleRegistry()->getRegisteredFormats();
        foreach ($registeredFormats as $styleId) {
            $numFmtId = $this->getStyleRegistry()->getFormatIdForStyleId($styleId);

            //Built-in formats do not need to be declared, skip them
            if ($numFmtId < 164) {
                continue;
            }

            /** @var Style $style */
            $style = $this->styleRegistry->getStyleFromStyleId($styleId);
            $format = $style->getFormat();
            $tags[] = '<numFmt numFmtId="' . $numFmtId . '" formatCode="' . $format . '"/>';
        }
        $content = '<numFmts count="' . \count($tags) . '">';
        $content .= \implode('', $tags);
        $content .= '</numFmts>';

        return $content;
    }

    /**
     * Returns the content of the "<fonts>" section.
     */
    protected function getFontsSectionContent(): string
    {
        $registeredStyles = $this->styleRegistry->getRegisteredStyles();

        $content = '<fonts count="' . \count($registeredStyles) . '">';

        /** @var Style $style */
        foreach ($registeredStyles as $style) {
            $content .= '<font>';

            $content .= '<sz val="' . $style->getFontSize() . '"/>';
            $content .= '<color rgb="' . Color::toARGB($style->getFontColor()) . '"/>';
            $content .= '<name val="' . $style->getFontName() . '"/>';

            if ($style->isFontBold()) {
                $content .= '<b/>';
            }
            if ($style->isFontItalic()) {
                $content .= '<i/>';
            }
            if ($style->isFontUnderline()) {
                $content .= '<u/>';
            }
            if ($style->isFontStrikethrough()) {
                $content .= '<strike/>';
            }

            $content .= '</font>';
        }

        $content .= '</fonts>';

        return $content;
    }

    /**
     * Returns the content of the "<fills>" section.
     */
    protected function getFillsSectionContent(): string
    {
        $registeredFills = $this->getStyleRegistry()->getRegisteredFills();

        // Excel reserves two default fills
        $fillsCount = \count($registeredFills) + 2;
        $content = \sprintf('<fills count="%d">', $fillsCount);

        $content .= '<fill><patternFill patternType="none"/></fill>';
        $content .= '<fill><patternFill patternType="gray125"/></fill>';

        // The other fills are actually registered by setting a background color
        foreach ($registeredFills as $styleId) {
            /** @var Style $style */
            $style = $this->styleRegistry->getStyleFromStyleId($styleId);

            $backgroundColor = $style->getBackgroundColor();
            $content .= \sprintf(
                '<fill><patternFill patternType="solid"><fgColor rgb="%s"/></patternFill></fill>',
                $backgroundColor
            );
        }

        $content .= '</fills>';

        return $content;
    }

    /**
     * Returns the content of the "<borders>" section.
     */
    protected function getBordersSectionContent(): string
    {
        $registeredBorders = $this->getStyleRegistry()->getRegisteredBorders();

        // There is one default border with index 0
        $borderCount = \count($registeredBorders) + 1;

        $content = '<borders count="' . $borderCount . '">';

        // Default border starting at index 0
        $content .= '<border><left/><right/><top/><bottom/></border>';

        foreach ($registeredBorders as $styleId) {
            /** @var Style $style */
            $style = $this->styleRegistry->getStyleFromStyleId($styleId);
            $border = $style->getBorder();
            $content .= '<border>';

            // @link https://github.com/box/spout/issues/271
            $sortOrder = ['left', 'right', 'top', 'bottom'];

            foreach ($sortOrder as $partName) {
                if ($border->hasPart($partName)) {
                    /** @var BorderPart $part */
                    $part = $border->getPart($partName);
                    $content .= BorderHelper::serializeBorderPart($part);
                }
            }

            $content .= '</border>';
        }

        $content .= '</borders>';

        return $content;
    }

    /**
     * Returns the content of the "<cellStyleXfs>" section.
     */
    protected function getCellStyleXfsSectionContent(): string
    {
        return <<<'EOD'
<cellStyleXfs count="1">
    <xf borderId="0" fillId="0" fontId="0" numFmtId="0"/>
</cellStyleXfs>
EOD;
    }

    /**
     * Returns the content of the "<cellXfs>" section.
     */
    protected function getCellXfsSectionContent(): string
    {
        $registeredStyles = $this->styleRegistry->getRegisteredStyles();

        $content = '<cellXfs count="' . \count($registeredStyles) . '">';

        foreach ($registeredStyles as $style) {
            $styleId = $style->getId();
            $fillId = $this->getFillIdForStyleId($styleId);
            $borderId = $this->getBorderIdForStyleId($styleId);
            $numFmtId = $this->getFormatIdForStyleId($styleId);

            $content .= '<xf numFmtId="' . $numFmtId . '" fontId="' . $styleId . '" fillId="' . $fillId . '" borderId="' . $borderId . '" xfId="0"';

            if ($style->shouldApplyFont()) {
                $content .= ' applyFont="1"';
            }

            $content .= \sprintf(' applyBorder="%d"', $style->shouldApplyBorder() ? 1 : 0);

            if ($style->shouldApplyCellAlignment() || $style->shouldWrapText()) {
                $content .= ' applyAlignment="1">';
                $content .= '<alignment';
                if ($style->shouldApplyCellAlignment()) {
                    $content .= \sprintf(' horizontal="%s"', $style->getCellAlignment());
                }
                if ($style->shouldWrapText()) {
                    $content .= ' wrapText="1"';
                }
                $content .= '/>';
                $content .= '</xf>';
            } else {
                $content .= '/>';
            }
        }

        $content .= '</cellXfs>';

        return $content;
    }

    /**
     * Returns the fill ID associated to the given style ID.
     * For the default style, we don't a fill.
     */
    private function getFillIdForStyleId(int $styleId): int
    {
        // For the default style (ID = 0), we don't want to override the fill.
        // Otherwise all cells of the spreadsheet will have a background color.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->getStyleRegistry()->getFillIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the fill ID associated to the given style ID.
     * For the default style, we don't a border.
     */
    private function getBorderIdForStyleId(int $styleId): int
    {
        // For the default style (ID = 0), we don't want to override the border.
        // Otherwise all cells of the spreadsheet will have a border.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->getStyleRegistry()->getBorderIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the format ID associated to the given style ID.
     * For the default style use general format.
     */
    private function getFormatIdForStyleId(int $styleId): int
    {
        // For the default style (ID = 0), we don't want to override the format.
        // Otherwise all cells of the spreadsheet will have a format.
        $isDefaultStyle = ($styleId === 0);

        return $isDefaultStyle ? 0 : ($this->getStyleRegistry()->getFormatIdForStyleId($styleId) ?: 0);
    }

    /**
     * Returns the content of the "<cellStyles>" section.
     */
    protected function getCellStylesSectionContent(): string
    {
        return <<<'EOD'
<cellStyles count="1">
    <cellStyle builtinId="0" name="Normal" xfId="0"/>
</cellStyles>
EOD;
    }
}
