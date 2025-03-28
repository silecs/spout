<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

use Box\Spout\Common\Entity\Style\BorderPart;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\Common\Entity\Worksheet;
use Box\Spout\Writer\ODS\Helper\BorderHelper;

/**
 * Class StyleManager
 * Manages styles to be applied to a cell
 */
class StyleManager extends \Box\Spout\Writer\Common\Manager\Style\StyleManager
{
    /**
     * Returns the content of the "styles.xml" file, given a list of styles.
     *
     * @param int $numWorksheets Number of worksheets created
     */
    public function getStylesXMLFileContent(int $numWorksheets): string
    {
        $content = <<<'EOD'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<office:document-styles office:version="1.2" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:msoxl="http://schemas.microsoft.com/office/excel/formula" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
EOD;

        $content .= $this->getFontFaceSectionContent();
        $content .= $this->getStylesSectionContent();
        $content .= $this->getAutomaticStylesSectionContent($numWorksheets);
        $content .= $this->getMasterStylesSectionContent($numWorksheets);

        $content .= <<<'EOD'
</office:document-styles>
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
     * Returns the content of the "<office:font-face-decls>" section, inside "styles.xml" file.
     */
    protected function getFontFaceSectionContent(): string
    {
        $content = '<office:font-face-decls>';
        foreach ($this->getStyleRegistry()->getUsedFonts() as $fontName) {
            $content .= '<style:font-face style:name="' . $fontName . '" svg:font-family="' . $fontName . '"/>';
        }
        $content .= '</office:font-face-decls>';

        return $content;
    }

    /**
     * Returns the content of the "<office:styles>" section, inside "styles.xml" file.
     */
    protected function getStylesSectionContent(): string
    {
        $defaultStyle = $this->getDefaultStyle();

        return <<<EOD
<office:styles>
    <number:number-style style:name="N0">
        <number:number number:min-integer-digits="1"/>
    </number:number-style>
    <style:style style:data-style-name="N0" style:family="table-cell" style:name="Default">
        <style:table-cell-properties fo:background-color="transparent" style:vertical-align="automatic"/>
        <style:text-properties fo:color="#{$defaultStyle->getFontColor()}"
                               fo:font-size="{$defaultStyle->getFontSize()}pt" style:font-size-asian="{$defaultStyle->getFontSize()}pt" style:font-size-complex="{$defaultStyle->getFontSize()}pt"
                               style:font-name="{$defaultStyle->getFontName()}" style:font-name-asian="{$defaultStyle->getFontName()}" style:font-name-complex="{$defaultStyle->getFontName()}"/>
    </style:style>
</office:styles>
EOD;
    }

    /**
     * Returns the content of the "<office:automatic-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     */
    protected function getAutomaticStylesSectionContent(int $numWorksheets): string
    {
        $content = '<office:automatic-styles>';

        for ($i = 1; $i <= $numWorksheets; $i++) {
            $content .= <<<EOD
<style:page-layout style:name="pm$i">
    <style:page-layout-properties style:first-page-number="continue" style:print="objects charts drawings" style:table-centering="none"/>
    <style:header-style/>
    <style:footer-style/>
</style:page-layout>
EOD;
        }

        $content .= '</office:automatic-styles>';

        return $content;
    }

    /**
     * Returns the content of the "<office:master-styles>" section, inside "styles.xml" file.
     *
     * @param int $numWorksheets Number of worksheets created
     */
    protected function getMasterStylesSectionContent(int$numWorksheets): string
    {
        $content = '<office:master-styles>';

        for ($i = 1; $i <= $numWorksheets; $i++) {
            $content .= <<<EOD
<style:master-page style:name="mp$i" style:page-layout-name="pm$i">
    <style:header/>
    <style:header-left style:display="false"/>
    <style:footer/>
    <style:footer-left style:display="false"/>
</style:master-page>
EOD;
        }

        $content .= '</office:master-styles>';

        return $content;
    }

    /**
     * Returns the contents of the "<office:font-face-decls>" section, inside "content.xml" file.
     */
    public function getContentXmlFontFaceSectionContent(): string
    {
        $content = '<office:font-face-decls>';
        foreach ($this->getStyleRegistry()->getUsedFonts() as $fontName) {
            $content .= '<style:font-face style:name="' . $fontName . '" svg:font-family="' . $fontName . '"/>';
        }
        $content .= '</office:font-face-decls>';

        return $content;
    }

    /**
     * Returns the contents of the "<office:automatic-styles>" section, inside "content.xml" file.
     *
     * @param Worksheet[] $worksheets
     */
    public function getContentXmlAutomaticStylesSectionContent(array $worksheets): string
    {
        $content = '<office:automatic-styles>';

        foreach ($this->styleRegistry->getRegisteredStyles() as $style) {
            $content .= $this->getStyleSectionContent($style);
        }

        $content .= <<<'EOD'
<style:style style:family="table-column" style:name="co1">
    <style:table-column-properties fo:break-before="auto"/>
</style:style>
<style:style style:family="table-row" style:name="ro1">
    <style:table-row-properties fo:break-before="auto" style:row-height="15pt" style:use-optimal-row-height="true"/>
</style:style>
EOD;

        foreach ($worksheets as $worksheet) {
            $worksheetId = $worksheet->getId();
            $isSheetVisible = $worksheet->getExternalSheet()->isVisible() ? 'true' : 'false';

            $content .= <<<EOD
<style:style style:family="table" style:master-page-name="mp$worksheetId" style:name="ta$worksheetId">
    <style:table-properties style:writing-mode="lr-tb" table:display="$isSheetVisible"/>
</style:style>
EOD;
        }

        $content .= '</office:automatic-styles>';

        return $content;
    }

    /**
     * Returns the contents of the "<style:style>" section, inside "<office:automatic-styles>" section
     */
    protected function getStyleSectionContent(Style $style): string
    {
        $styleIndex = $style->getId() + 1; // 1-based

        $content = '<style:style style:data-style-name="N0" style:family="table-cell" style:name="ce' . $styleIndex . '" style:parent-style-name="Default">';

        $content .= $this->getTextPropertiesSectionContent($style);
        $content .= $this->getParagraphPropertiesSectionContent($style);
        $content .= $this->getTableCellPropertiesSectionContent($style);

        $content .= '</style:style>';

        return $content;
    }

    /**
     * Returns the contents of the "<style:text-properties>" section, inside "<style:style>" section
     */
    private function getTextPropertiesSectionContent(Style $style): string
    {
        if (!$style->shouldApplyFont()) {
            return '';
        }

        return '<style:text-properties '
            . $this->getFontSectionContent($style)
            . '/>';
    }

    /**
     * Returns the contents of the fonts definition section, inside "<style:text-properties>" section
     */
    private function getFontSectionContent(Style $style): string
    {
        $defaultStyle = $this->getDefaultStyle();
        $content = '';

        $fontColor = $style->getFontColor();
        if ($fontColor !== $defaultStyle->getFontColor()) {
            $content .= ' fo:color="#' . $fontColor . '"';
        }

        $fontName = $style->getFontName();
        if ($fontName !== $defaultStyle->getFontName()) {
            $content .= ' style:font-name="' . $fontName . '" style:font-name-asian="' . $fontName . '" style:font-name-complex="' . $fontName . '"';
        }

        $fontSize = $style->getFontSize();
        if ($fontSize !== $defaultStyle->getFontSize()) {
            $content .= ' fo:font-size="' . $fontSize . 'pt" style:font-size-asian="' . $fontSize . 'pt" style:font-size-complex="' . $fontSize . 'pt"';
        }

        if ($style->isFontBold()) {
            $content .= ' fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"';
        }
        if ($style->isFontItalic()) {
            $content .= ' fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"';
        }
        if ($style->isFontUnderline()) {
            $content .= ' style:text-underline-style="solid" style:text-underline-type="single"';
        }
        if ($style->isFontStrikethrough()) {
            $content .= ' style:text-line-through-style="solid"';
        }

        return $content;
    }

    /**
     * Returns the contents of the "<style:paragraph-properties>" section, inside "<style:style>" section
     */
    private function getParagraphPropertiesSectionContent(Style $style): string
    {
        if (!$style->shouldApplyCellAlignment()) {
            return '';
        }

        return '<style:paragraph-properties '
            . $this->getCellAlignmentSectionContent($style)
            . '/>';
    }

    /**
     * Returns the contents of the cell alignment definition for the "<style:paragraph-properties>" section
     */
    private function getCellAlignmentSectionContent(Style $style): string
    {
        return \sprintf(
            ' fo:text-align="%s" ',
            $this->transformCellAlignment($style->getCellAlignment())
        );
    }

    /**
     * Even though "left" and "right" alignments are part of the spec, and interpreted
     * respectively as "start" and "end", using the recommended values increase compatibility
     * with software that will read the created ODS file.
     */
    private function transformCellAlignment(string $cellAlignment): string
    {
        switch ($cellAlignment) {
            case CellAlignment::LEFT: return 'start';
            case CellAlignment::RIGHT: return 'end';
            default: return $cellAlignment;
        }
    }

    /**
     * Returns the contents of the "<style:table-cell-properties>" section, inside "<style:style>" section
     */
    private function getTableCellPropertiesSectionContent(Style $style): string
    {
        $content = '<style:table-cell-properties ';

        if ($style->shouldWrapText()) {
            $content .= $this->getWrapTextXMLContent();
        }

        if ($style->shouldApplyBorder()) {
            $content .= $this->getBorderXMLContent($style);
        }

        if ($style->shouldApplyBackgroundColor()) {
            $content .= $this->getBackgroundColorXMLContent($style);
        }

        $content .= '/>';

        return $content;
    }

    /**
     * Returns the contents of the wrap text definition for the "<style:table-cell-properties>" section
     */
    private function getWrapTextXMLContent(): string
    {
        return ' fo:wrap-option="wrap" style:vertical-align="automatic" ';
    }

    /**
     * Returns the contents of the borders definition for the "<style:table-cell-properties>" section
     */
    private function getBorderXMLContent(Style $style): string
    {
        $borders = \array_map(function (BorderPart $borderPart) {
            return BorderHelper::serializeBorderPart($borderPart);
        }, $style->getBorder()->getParts());

        return \sprintf(' %s ', \implode(' ', $borders));
    }

    /**
     * Returns the contents of the background color definition for the "<style:table-cell-properties>" section
     */
    private function getBackgroundColorXMLContent(Style $style): string
    {
        return \sprintf(' fo:background-color="#%s" ', $style->getBackgroundColor());
    }
}
