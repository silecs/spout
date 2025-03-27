<?php

namespace Box\Spout\Writer\ODS\Manager;

use Box\Spout\Common\Manager\OptionsManagerAbstract;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Entity\Options;

/**
 * Class OptionsManager
 * ODS Writer options manager
 */
class OptionsManager extends OptionsManagerAbstract
{
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaultOptions(): void
    {
        $this->setOption(Options::TEMP_FOLDER, \sys_get_temp_dir());
        $this->setOption(Options::DEFAULT_ROW_STYLE, $this->styleBuilder->build());
        $this->setOption(Options::SHOULD_CREATE_NEW_SHEETS_AUTOMATICALLY, true);
    }
}
