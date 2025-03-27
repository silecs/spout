<?php

namespace Box\Spout\Reader\ODS\Creator;

use Box\Spout\Reader\ODS\Helper\CellValueFormatter;
use Box\Spout\Reader\ODS\Helper\SettingsHelper;

/**
 * Class HelperFactory
 * Factory to create helpers
 */
class HelperFactory extends \Box\Spout\Common\Creator\HelperFactory
{
    /**
     * @param bool $shouldFormatDates Whether date/time values should be returned as PHP objects or be formatted as strings
     */
    public function createCellValueFormatter(bool $shouldFormatDates): CellValueFormatter
    {
        $escaper = $this->createStringsEscaper();

        return new CellValueFormatter($shouldFormatDates, $escaper);
    }

    public function createSettingsHelper(InternalEntityFactory $entityFactory): SettingsHelper
    {
        return new SettingsHelper($entityFactory);
    }

    public function createStringsEscaper(): \Box\Spout\Common\Helper\Escaper\ODS
    {
        /* @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return new \Box\Spout\Common\Helper\Escaper\ODS();
    }
}
