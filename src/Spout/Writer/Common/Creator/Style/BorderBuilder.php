<?php

namespace Box\Spout\Writer\Common\Creator\Style;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\BorderPart;
use Box\Spout\Common\Entity\Style\Color;

class BorderBuilder
{
    protected Border $border;

    public function __construct()
    {
        $this->border = new Border();
    }

    /**
     * @param string $color Border A RGB color code
     * @param string $width Border width @see BorderPart::allowedWidths
     * @param string $style Border style @see BorderPart::allowedStyles
     */
    public function setBorderTop(string $color = Color::BLACK, string $width = Border::WIDTH_MEDIUM, string $style = Border::STYLE_SOLID): self
    {
        $this->border->addPart(new BorderPart(Border::TOP, $color, $width, $style));

        return $this;
    }

    /**
     * @param string $color Border A RGB color code
     * @param string $width Border width @see BorderPart::allowedWidths
     * @param string $style Border style @see BorderPart::allowedStyles
     */
    public function setBorderRight(string $color = Color::BLACK, string $width = Border::WIDTH_MEDIUM, string $style = Border::STYLE_SOLID): self
    {
        $this->border->addPart(new BorderPart(Border::RIGHT, $color, $width, $style));

        return $this;
    }

    /**
     * @param string $color Border A RGB color code
     * @param string $width Border width @see BorderPart::allowedWidths
     * @param string $style Border style @see BorderPart::allowedStyles
     */
    public function setBorderBottom(string $color = Color::BLACK, string $width = Border::WIDTH_MEDIUM, string $style = Border::STYLE_SOLID): self
    {
        $this->border->addPart(new BorderPart(Border::BOTTOM, $color, $width, $style));

        return $this;
    }

    /**
     * @param string $color Border A RGB color code
     * @param string $width Border width @see BorderPart::allowedWidths
     * @param string $style Border style @see BorderPart::allowedStyles
     */
    public function setBorderLeft(string $color = Color::BLACK, string $width = Border::WIDTH_MEDIUM, string $style = Border::STYLE_SOLID): self
    {
        $this->border->addPart(new BorderPart(Border::LEFT, $color, $width, $style));

        return $this;
    }

    public function build(): Border
    {
        return $this->border;
    }
}
