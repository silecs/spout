<?php

namespace Box\Spout\Writer\XLSX\Manager;

use Box\Spout\Common\Manager\OptionsManagerAbstract;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Entity\Options;

/**
 * Class OptionsManager
 * XLSX Writer options manager
 */
class OptionsManager extends OptionsManagerAbstract
{
    /** Default style font values */
    public const DEFAULT_FONT_SIZE = 12;
    public const DEFAULT_FONT_NAME = 'Calibri';

    protected StyleBuilder $styleBuilder;

    public function __construct(StyleBuilder $styleBuilder)
    {
        $this->styleBuilder = $styleBuilder;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedOptions(): array
    {
        return [
            Options::TEMP_FOLDER,
            Options::DEFAULT_ROW_STYLE,
            Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY,
            Options::SHOULD_USE_INLINE_STRINGS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(): void
    {
        $defaultRowStyle = $this->styleBuilder
            ->setFontSize(self::DEFAULT_FONT_SIZE)
            ->setFontName(self::DEFAULT_FONT_NAME)
            ->build();

        $this->setOption(Options::TEMP_FOLDER, \sys_get_temp_dir());
        $this->setOption(Options::DEFAULT_ROW_STYLE, $defaultRowStyle);
        $this->setOption(Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY, true);
        $this->setOption(Options::SHOULD_USE_INLINE_STRINGS, true);
    }
}
