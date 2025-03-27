<?php

namespace Box\Spout\Common\Entity\Style;

/**
 * Class Style
 * Represents a style to be applied to a cell
 */
class Style
{
    /** Default values */
    public const DEFAULT_FONT_SIZE = 11;
    public const DEFAULT_FONT_COLOR = Color::BLACK;
    public const DEFAULT_FONT_NAME = 'Arial';

    /** @var int|null Style ID */
    private ?int $id = null;

    private bool $fontBold = false;

    /** @var bool Whether the bold property was set */
    private bool $hasSetFontBold = false;

    private bool $fontItalic = false;

    /** @var bool Whether the italic property was set */
    private bool $hasSetFontItalic = false;

    private bool $fontUnderline = false;

    /** @var bool Whether the underline property was set */
    private bool $hasSetFontUnderline = false;

    private bool $fontStrikethrough = false;

    /** @var bool Whether the strikethrough property was set */
    private bool $hasSetFontStrikethrough = false;

    private int $fontSize = self::DEFAULT_FONT_SIZE;

    /** @var bool Whether the font size property was set */
    private bool $hasSetFontSize = false;

    private string $fontColor = self::DEFAULT_FONT_COLOR;

    /** @var bool Whether the font color property was set */
    private bool $hasSetFontColor = false;

    private string $fontName = self::DEFAULT_FONT_NAME;

    /** @var bool Whether the font name property was set */
    private bool $hasSetFontName = false;

    /** @var bool Whether specific font properties should be applied */
    private bool $shouldApplyFont = false;

    private bool $shouldApplyCellAlignment = false;

    private string $cellAlignment = '';

    /** @var bool Whether the cell alignment property was set */
    private bool $hasSetCellAlignment = false;

    /** @var bool Whether the text should wrap in the cell (useful for long or multi-lines text) */
    private bool $shouldWrapText = false;

    /** @var bool Whether the wrap text property was set */
    private bool $hasSetWrapText = false;

    private ?Border $border = null;

    /** @var bool Whether border properties should be applied */
    private bool $shouldApplyBorder = false;

    private string $backgroundColor = '';

    private bool $hasSetBackgroundColor = false;

    private ?string $format = null;

    private bool $hasSetFormat = false;

    private bool $isRegistered = false;

    private bool $isEmpty = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Style
    {
        $this->id = $id;

        return $this;
    }

    public function getBorder(): ?Border
    {
        return $this->border;
    }

    public function setBorder(Border $border): Style
    {
        $this->shouldApplyBorder = true;
        $this->border = $border;
        $this->isEmpty = false;

        return $this;
    }

    public function shouldApplyBorder(): bool
    {
        return $this->shouldApplyBorder;
    }

    public function isFontBold(): bool
    {
        return $this->fontBold;
    }

    public function setFontBold(): Style
    {
        $this->fontBold = true;
        $this->hasSetFontBold = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontBold(): bool
    {
        return $this->hasSetFontBold;
    }

    public function isFontItalic(): bool
    {
        return $this->fontItalic;
    }

    public function setFontItalic(): Style
    {
        $this->fontItalic = true;
        $this->hasSetFontItalic = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontItalic(): bool
    {
        return $this->hasSetFontItalic;
    }

    public function isFontUnderline(): bool
    {
        return $this->fontUnderline;
    }

    public function setFontUnderline(): Style
    {
        $this->fontUnderline = true;
        $this->hasSetFontUnderline = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontUnderline(): bool
    {
        return $this->hasSetFontUnderline;
    }

    public function isFontStrikethrough(): bool
    {
        return $this->fontStrikethrough;
    }

    public function setFontStrikethrough(): Style
    {
        $this->fontStrikethrough = true;
        $this->hasSetFontStrikethrough = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontStrikethrough(): bool
    {
        return $this->hasSetFontStrikethrough;
    }

    public function getFontSize(): int
    {
        return $this->fontSize;
    }

    /**
     * @param int $fontSize Font size, in pixels
     */
    public function setFontSize(int $fontSize): Style
    {
        $this->fontSize = $fontSize;
        $this->hasSetFontSize = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontSize(): bool
    {
        return $this->hasSetFontSize;
    }

    public function getFontColor(): string
    {
        return $this->fontColor;
    }

    /**
     * Sets the font color.
     *
     * @param string $fontColor ARGB color (@see Color)
     */
    public function setFontColor(string $fontColor): Style
    {
        $this->fontColor = $fontColor;
        $this->hasSetFontColor = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontColor(): bool
    {
        return $this->hasSetFontColor;
    }

    public function getFontName(): string
    {
        return $this->fontName;
    }

    public function setFontName(string $fontName): Style
    {
        $this->fontName = $fontName;
        $this->hasSetFontName = true;
        $this->shouldApplyFont = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetFontName(): bool
    {
        return $this->hasSetFontName;
    }

    public function getCellAlignment(): string
    {
        return $this->cellAlignment;
    }

    public function setCellAlignment(string $cellAlignment): Style
    {
        $this->cellAlignment = $cellAlignment;
        $this->hasSetCellAlignment = true;
        $this->shouldApplyCellAlignment = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetCellAlignment(): bool
    {
        return $this->hasSetCellAlignment;
    }

    public function shouldApplyCellAlignment(): bool
    {
        return $this->shouldApplyCellAlignment;
    }

    public function shouldWrapText(): bool
    {
        return $this->shouldWrapText;
    }

    /**
     * @param bool $shouldWrap Should the text be wrapped
     */
    public function setShouldWrapText(bool $shouldWrap = true): Style
    {
        $this->shouldWrapText = $shouldWrap;
        $this->hasSetWrapText = true;
        $this->isEmpty = false;

        return $this;
    }

    public function hasSetWrapText(): bool
    {
        return $this->hasSetWrapText;
    }

    /**
     * @return bool Whether specific font properties should be applied
     */
    public function shouldApplyFont(): bool
    {
        return $this->shouldApplyFont;
    }

    /**
     * Sets the background color
     * @param string $color ARGB color (@see Color)
     */
    public function setBackgroundColor(string $color): Style
    {
        $this->hasSetBackgroundColor = true;
        $this->backgroundColor = $color;
        $this->isEmpty = false;

        return $this;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    /**
     * @return bool Whether the background color should be applied
     */
    public function shouldApplyBackgroundColor(): bool
    {
        return $this->hasSetBackgroundColor;
    }

    public function setFormat(string $format): Style
    {
        $this->hasSetFormat = true;
        $this->format = $format;
        $this->isEmpty = false;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return bool Whether format should be applied
     */
    public function shouldApplyFormat(): bool
    {
        return $this->hasSetFormat;
    }

    public function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    public function markAsRegistered(?int $id): void
    {
        $this->setId($id);
        $this->isRegistered = true;
    }

    public function unmarkAsRegistered(): void
    {
        $this->setId(0);
        $this->isRegistered = false;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }
}
