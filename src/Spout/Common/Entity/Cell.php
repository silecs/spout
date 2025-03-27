<?php

namespace Box\Spout\Common\Entity;

use Box\Spout\Common\Entity\Style\Style;

/**
 * Class Cell
 */
class Cell
{
    /**
     * Numeric cell type (whole numbers, fractional numbers, dates)
     */
    public const TYPE_NUMERIC = 0;

    /**
     * String (text) cell type
     */
    public const TYPE_STRING = 1;

    /**
     * Formula cell type
     * Not used at the moment
     */
    public const TYPE_FORMULA = 2;

    /**
     * Empty cell type
     */
    public const TYPE_EMPTY = 3;

    /**
     * Boolean cell type
     */
    public const TYPE_BOOLEAN = 4;

    /**
     * Date cell type
     */
    public const TYPE_DATE = 5;

    /**
     * Error cell type
     */
    public const TYPE_ERROR = 6;

    /**
     * The value of this cell
     */
    protected mixed $value;

    /**
     * The cell type
     */
    protected ?int $type;

    /**
     * The cell style
     */
    protected ?Style $style;

    public function __construct(mixed $value, ?Style $style = null)
    {
        $this->value = $value;
        $this->type = $this->detectType($value);
        $this->style = $style ?: new Style();
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
        $this->type = $this->detectType($value);
    }

    public function getValue(): mixed
    {
        return !$this->isError() ? $this->value : null;
    }

    public function getValueEvenIfError(): mixed
    {
        return $this->value;
    }

    public function setStyle(?Style $style)
    {
        $this->style = $style ?: new Style();
    }

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type)
    {
        $this->type = $type;
    }

    public function isBoolean(): bool
    {
        return $this->type === self::TYPE_BOOLEAN;
    }

    public function isEmpty(): bool
    {
        return $this->type === self::TYPE_EMPTY;
    }

    public function isNumeric(): bool
    {
        return $this->type === self::TYPE_NUMERIC;
    }

    public function isString(): bool
    {
        return $this->type === self::TYPE_STRING;
    }

    public function isDate(): bool
    {
        return $this->type === self::TYPE_DATE;
    }

    public function isError(): bool
    {
        return $this->type === self::TYPE_ERROR;
    }

    /**
     * Get the current value type
     */
    protected function detectType(mixed $value): int
    {
        if ($value === null || $value === '') {
            return self::TYPE_EMPTY;
        }
        switch (\gettype($value)) {
            case 'boolean':
                return self::TYPE_BOOLEAN;
            case 'double':
            case 'integer':
                return self::TYPE_NUMERIC;
            case 'string':
                return self::TYPE_STRING;
            case 'object':
                if (
                    $value instanceof \DateTimeInterface ||
                    $value instanceof \DateInterval
                ) {
                    return self::TYPE_DATE;
                }
        }

        return self::TYPE_ERROR;
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
