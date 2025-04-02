<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

enum DefaultStyle: string
{
    case BOOLEAN = 'N99';
    case DATE_EUROPE = 'N36';
    case DATE_ISO = 'N49';
    case DATETIME_EUROPE = 'N72';
    case DATETIME_ISO = 'N73';
    case NUMERIC = 'N0';
    case TEXT = 'N100';
}
