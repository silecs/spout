<?php

namespace Box\Spout\Writer\XLSX\Manager\Style;

use Box\Spout\Common\Entity\Style\Style;

/**
 * Class StyleRegistry
 * Registry for all used styles
 */
class StyleRegistry extends \Box\Spout\Writer\Common\Manager\Style\StyleRegistry
{
    /**
     * @see https://msdn.microsoft.com/en-us/library/ff529597(v=office.12).aspx
     * @var array Mapping between built-in format and the associated numFmtId
     */
    protected static array $builtinNumFormatToIdMapping = [
        'General' => 0,
        '0' => 1,
        '0.00' => 2,
        '#,##0' => 3,
        '#,##0.00' => 4,
        '$#,##0,\-$#,##0' => 5,
        '$#,##0,[Red]\-$#,##0' => 6,
        '$#,##0.00,\-$#,##0.00' => 7,
        '$#,##0.00,[Red]\-$#,##0.00' => 8,
        '0%' => 9,
        '0.00%' => 10,
        '0.00E+00' => 11,
        '# ?/?' => 12,
        '# ??/??' => 13,
        'mm-dd-yy' => 14,
        'd-mmm-yy' => 15,
        'd-mmm' => 16,
        'mmm-yy' => 17,
        'h:mm AM/PM' => 18,
        'h:mm:ss AM/PM' => 19,
        'h:mm' => 20,
        'h:mm:ss' => 21,
        'm/d/yy h:mm' => 22,

        '#,##0 ,(#,##0)' => 37,
        '#,##0 ,[Red](#,##0)' => 38,
        '#,##0.00,(#,##0.00)' => 39,
        '#,##0.00,[Red](#,##0.00)' => 40,

        '_("$"* #,##0.00_),_("$"* \(#,##0.00\),_("$"* "-"??_),_(@_)' => 44,
        'mm:ss' => 45,
        '[h]:mm:ss' => 46,
        'mm:ss.0' => 47,

        '##0.0E+0' => 48,
        '@' => 49,

        '[$-404]e/m/d' => 27,
        'm/d/yy' => 30,
        't0' => 59,
        't0.00' => 60,
        't#,##0' => 61,
        't#,##0.00' => 62,
        't0%' => 67,
        't0.00%' => 68,
        't# ?/?' => 69,
        't# ??/??' => 70,
    ];

    protected array $registeredFormats = [];

    /**
     * @var array [STYLE_ID] => [FORMAT_ID] maps a style to a format declaration
     */
    protected array $styleIdToFormatsMappingTable = [];

    /**
     * If the numFmtId is lower than 0xA4 (164 in decimal)
     * then it's a built-in number format.
     * Since Excel is the dominant vendor - we play along here
     *
     * @var int The fill index counter for custom fills.
     */
    protected int $formatIndex = 164;

    protected array $registeredFills = [];

    /**
     * @var array [STYLE_ID] => [FILL_ID] maps a style to a fill declaration
     */
    protected array $styleIdToFillMappingTable = [];

    /**
     * Excel preserves two default fills with index 0 and 1
     * Since Excel is the dominant vendor - we play along here
     *
     * @var int The fill index counter for custom fills.
     */
    protected int $fillIndex = 2;

    protected array $registeredBorders = [];

    /**
     * @var array [STYLE_ID] => [BORDER_ID] maps a style to a border declaration
     */
    protected array $styleIdToBorderMappingTable = [];

    /**
     * XLSX specific operations on the registered styles
     */
    public function registerStyle(Style $style): Style
    {
        if ($style->isRegistered()) {
            return $style;
        }

        $registeredStyle = parent::registerStyle($style);
        $this->registerFill($registeredStyle);
        $this->registerFormat($registeredStyle);
        $this->registerBorder($registeredStyle);

        return $registeredStyle;
    }

    /**
     * Register a format definition
     */
    protected function registerFormat(Style $style): void
    {
        $styleId = $style->getId();

        $format = $style->getFormat();
        if ($format) {
            $isFormatRegistered = isset($this->registeredFormats[$format]);

            // We need to track the already registered format definitions
            if ($isFormatRegistered) {
                $registeredStyleId = $this->registeredFormats[$format];
                $registeredFormatId = $this->styleIdToFormatsMappingTable[$registeredStyleId];
                $this->styleIdToFormatsMappingTable[$styleId] = $registeredFormatId;
            } else {
                $this->registeredFormats[$format] = $styleId;

                $id = self::$builtinNumFormatToIdMapping[$format] ?? $this->formatIndex++;
                $this->styleIdToFormatsMappingTable[$styleId] = $id;
            }
        } else {
            // The formatId maps a style to a format declaration
            // When there is no format definition - we default to 0 ( General )
            $this->styleIdToFormatsMappingTable[$styleId] = 0;
        }
    }

    /**
     * @return int|null Format ID associated to the given style ID
     */
    public function getFormatIdForStyleId(int $styleId): ?int
    {
        return $this->styleIdToFormatsMappingTable[$styleId] ?? null;
    }

    /**
     * Register a fill definition
     */
    private function registerFill(Style $style): void
    {
        $styleId = $style->getId();

        // Currently - only solid backgrounds are supported
        // so $backgroundColor is a scalar value (RGB Color)
        $backgroundColor = $style->getBackgroundColor();

        if ($backgroundColor) {
            $isBackgroundColorRegistered = isset($this->registeredFills[$backgroundColor]);

            // We need to track the already registered background definitions
            if ($isBackgroundColorRegistered) {
                $registeredStyleId = $this->registeredFills[$backgroundColor];
                $registeredFillId = $this->styleIdToFillMappingTable[$registeredStyleId];
                $this->styleIdToFillMappingTable[$styleId] = $registeredFillId;
            } else {
                $this->registeredFills[$backgroundColor] = $styleId;
                $this->styleIdToFillMappingTable[$styleId] = $this->fillIndex++;
            }
        } else {
            // The fillId maps a style to a fill declaration
            // When there is no background color definition - we default to 0
            $this->styleIdToFillMappingTable[$styleId] = 0;
        }
    }

    /**
     * @return int|null Fill ID associated to the given style ID
     */
    public function getFillIdForStyleId(int $styleId): ?int
    {
        return (isset($this->styleIdToFillMappingTable[$styleId])) ?
            $this->styleIdToFillMappingTable[$styleId] :
            null;
    }

    /**
     * Register a border definition
     */
    private function registerBorder(Style $style): void
    {
        $styleId = $style->getId();

        if ($style->shouldApplyBorder()) {
            $border = $style->getBorder();
            $serializedBorder = \serialize($border);

            $isBorderAlreadyRegistered = isset($this->registeredBorders[$serializedBorder]);

            if ($isBorderAlreadyRegistered) {
                $registeredStyleId = $this->registeredBorders[$serializedBorder];
                $registeredBorderId = $this->styleIdToBorderMappingTable[$registeredStyleId];
                $this->styleIdToBorderMappingTable[$styleId] = $registeredBorderId;
            } else {
                $this->registeredBorders[$serializedBorder] = $styleId;
                $this->styleIdToBorderMappingTable[$styleId] = \count($this->registeredBorders);
            }
        } else {
            // If no border should be applied - the mapping is the default border: 0
            $this->styleIdToBorderMappingTable[$styleId] = 0;
        }
    }

    /**
     * @return int|null Fill ID associated to the given style ID
     */
    public function getBorderIdForStyleId(int $styleId): ?int
    {
        return (isset($this->styleIdToBorderMappingTable[$styleId])) ?
            $this->styleIdToBorderMappingTable[$styleId] :
            null;
    }

    public function getRegisteredFills(): array
    {
        return $this->registeredFills;
    }

    public function getRegisteredBorders(): array
    {
        return $this->registeredBorders;
    }

    public function getRegisteredFormats(): array
    {
        return $this->registeredFormats;
    }
}
