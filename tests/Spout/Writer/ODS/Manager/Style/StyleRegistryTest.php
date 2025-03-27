<?php

namespace Box\Spout\Writer\ODS\Manager\Style;

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use PHPUnit\Framework\TestCase;

class StyleRegistryTest extends TestCase
{
    private function getStyleRegistry(): StyleRegistry
    {
        $defaultStyle = (new StyleBuilder())->build();

        return new StyleRegistry($defaultStyle);
    }

    public function testRegisterStyleKeepsTrackOfUsedFonts(): void
    {
        $styleRegistry = $this->getStyleRegistry();

        $this->assertCount(1, $styleRegistry->getUsedFonts(), 'There should only be the default font name');

        $style1 = (new StyleBuilder())->setFontName('MyFont1')->build();
        $styleRegistry->registerStyle($style1);

        $style2 = (new StyleBuilder())->setFontName('MyFont2')->build();
        $styleRegistry->registerStyle($style2);

        $this->assertCount(3, $styleRegistry->getUsedFonts(), 'There should be 3 fonts registered');
    }
}
