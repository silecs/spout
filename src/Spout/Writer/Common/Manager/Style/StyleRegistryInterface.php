<?php

namespace Box\Spout\Writer\Common\Manager\Style;

use Box\Spout\Common\Entity\Style\Style;

interface StyleRegistryInterface
{
    public function getRegisteredStyles(): array;

    public function getStyleFromStyleId(int $styleId): Style;

    public function registerStyle(Style $style): Style;

    public function serialize(Style $style): string;
}
