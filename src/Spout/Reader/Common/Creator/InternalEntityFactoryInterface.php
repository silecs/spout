<?php

namespace Box\Spout\Reader\Common\Creator;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;

/**
 * Interface EntityFactoryInterface
 */
interface InternalEntityFactoryInterface
{
    /**
     * @param Cell[] $cells
     */
    public function createRow(array $cells = []): Row;

    public function createCell(mixed $cellValue): Cell;
}
