<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

use Box\Spout\Common\Entity\Style\Style;

/**
 * Class StyleRegistry
 * Registry for all used styles
 */
class StyleRegistry extends \Box\Spout\Writer\Common\Manager\Style\StyleRegistry
{
    /** @var array<string, int> */
    private array $defaultStyles = [];

    /** @var array [FONT_NAME] => [] Map whose keys contain all the fonts used */
    protected array $usedFontsSet = [];

    public function __construct(Style $defaultStyle)
    {
        parent::__construct($defaultStyle);
        foreach (DefaultStyle::cases() as $case) {
            $style = new Style();
            $style->setFormat($case->value);
            $registered = $this->registerStyle($style);
            $this->defaultStyles[$case->value] = $registered->getId();
        }
    }

    public function getDefaultStyle(DefaultStyle $s): Style
    {
        $id = $this->defaultStyles[$s->value] ?? null;
        if ($id === null) {
            throw new \Exception("Uninitialized default style");
        }
        return $this->getStyleFromStyleId($id);
    }

    /**
     * Registers the given style as a used style.
     * Duplicate styles won't be registered more than once.
     *
     * @param Style $style The style to be registered
     * @return Style The registered style, updated with an internal ID.
     */
    public function registerStyle(Style $style): Style
    {
        if ($style->isRegistered()) {
            return $style;
        }

        $registeredStyle = parent::registerStyle($style);
        $this->usedFontsSet[$style->getFontName()] = true;

        return $registeredStyle;
    }

    /**
     * @return string[] List of used fonts name
     */
    public function getUsedFonts(): array
    {
        return \array_keys($this->usedFontsSet);
    }
}
