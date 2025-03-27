<?php

namespace Box\Spout\Writer\Common\Creator\Style;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Common\Exception\InvalidArgumentException;

/**
 * Class StyleBuilder
 * Builder to create new styles
 */
class StyleBuilder
{
    /** @var Style Style to be created */
    protected Style $style;

    public function __construct()
    {
        $this->style = new Style();
    }

    /**
     * Makes the font bold.
     */
    public function setFontBold(): self
    {
        $this->style->setFontBold();

        return $this;
    }

    /**
     * Makes the font italic.
     */
    public function setFontItalic(): self
    {
        $this->style->setFontItalic();

        return $this;
    }

    /**
     * Makes the font underlined.
     */
    public function setFontUnderline(): self
    {
        $this->style->setFontUnderline();

        return $this;
    }

    /**
     * Makes the font struck through.
     */
    public function setFontStrikethrough(): self
    {
        $this->style->setFontStrikethrough();

        return $this;
    }

    /**
     * Sets the font size.
     *
     * @param int $fontSize Font size, in pixels
     */
    public function setFontSize(int $fontSize): self
    {
        $this->style->setFontSize($fontSize);

        return $this;
    }

    /**
     * Sets the font color.
     *
     * @param string $fontColor ARGB color (@see Color)
     */
    public function setFontColor(string $fontColor): self
    {
        $this->style->setFontColor($fontColor);

        return $this;
    }

    /**
     * Sets the font name.
     *
     * @param string $fontName Name of the font to use
     */
    public function setFontName(string $fontName): self
    {
        $this->style->setFontName($fontName);

        return $this;
    }

    /**
     * Makes the text wrap in the cell if requested
     *
     * @param bool $shouldWrap Should the text be wrapped
     */
    public function setShouldWrapText(bool $shouldWrap = true): self
    {
        $this->style->setShouldWrapText($shouldWrap);

        return $this;
    }

    /**
     * Sets the cell alignment.
     *
     * @throws InvalidArgumentException If the given cell alignment is not valid
     */
    public function setCellAlignment(string $cellAlignment): self
    {
        if (!CellAlignment::isValid($cellAlignment)) {
            throw new InvalidArgumentException('Invalid cell alignment value');
        }

        $this->style->setCellAlignment($cellAlignment);

        return $this;
    }

    /**
     * Set a border
     */
    public function setBorder(Border $border): self
    {
        $this->style->setBorder($border);

        return $this;
    }

    /**
     *  Sets a background color
     *
     * @param string $color ARGB color (@see Color)
     */
    public function setBackgroundColor(string $color): self
    {
        $this->style->setBackgroundColor($color);

        return $this;
    }

    /**
     *  Sets a format
     *
     * @api
     */
    public function setFormat(string $format): self
    {
        $this->style->setFormat($format);

        return $this;
    }

    /**
     * Returns the configured style. The style is cached and can be reused.
     */
    public function build(): Style
    {
        return $this->style;
    }
}
