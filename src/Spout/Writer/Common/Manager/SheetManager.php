<?php

namespace Box\Spout\Writer\Common\Manager;

use Box\Spout\Writer\Common\Entity\Sheet;
use Box\Spout\Writer\Exception\InvalidSheetNameException;

/**
 * Class SheetManager
 * Sheet manager
 */
class SheetManager
{
    /** Sheet name should not exceed 31 characters */
    public const MAX_LENGTH_SHEET_NAME = 31;

    /** @var array Invalid characters that cannot be contained in the sheet name */
    private static array $INVALID_CHARACTERS_IN_SHEET_NAME = ['\\', '/', '?', '*', ':', '[', ']'];

    /** @var array Associative array [WORKBOOK_ID] => [[SHEET_INDEX] => [SHEET_NAME]] keeping track of sheets' name to enforce uniqueness per workbook */
    private static array $SHEETS_NAME_USED = [];

    /**
     * Throws an exception if the given sheet's name is not valid.
     * @see Sheet::setName for validity rules.
     *
     * @param Sheet $sheet The sheet whose future name is checked
     * @throws \Box\Spout\Writer\Exception\InvalidSheetNameException If the sheet's name is invalid.
     */
    public function throwIfNameIsInvalid(string $name, Sheet $sheet): void
    {
        $failedRequirements = [];
        $nameLength = mb_strlen($name);

        if (!$this->isNameUnique($name, $sheet)) {
            $failedRequirements[] = 'It should be unique';
        } else {
            if ($nameLength === 0) {
                $failedRequirements[] = 'It should not be blank';
            } else {
                if ($nameLength > self::MAX_LENGTH_SHEET_NAME) {
                    $failedRequirements[] = 'It should not exceed 31 characters';
                }

                if ($this->doesContainInvalidCharacters($name)) {
                    $failedRequirements[] = 'It should not contain these characters: \\ / ? * : [ or ]';
                }

                if ($this->doesStartOrEndWithSingleQuote($name)) {
                    $failedRequirements[] = 'It should not start or end with a single quote';
                }
            }
        }

        if (\count($failedRequirements) !== 0) {
            $errorMessage = "The sheet's name (\"$name\") is invalid. It did not respect these rules:\n - ";
            $errorMessage .= \implode("\n - ", $failedRequirements);
            throw new InvalidSheetNameException($errorMessage);
        }
    }

    /**
     * Returns whether the given name contains at least one invalid character.
     * @see Sheet::$INVALID_CHARACTERS_IN_SHEET_NAME for the full list.
     */
    private function doesContainInvalidCharacters(string $name): bool
    {
        return (\str_replace(self::$INVALID_CHARACTERS_IN_SHEET_NAME, '', $name) !== $name);
    }

    /**
     * Returns whether the given name starts or ends with a single quote
     */
    private function doesStartOrEndWithSingleQuote(string $name): bool
    {
        return str_starts_with($name, "'") || str_ends_with($name, "'");
    }

    /**
     * Returns whether the given name is unique.
     *
     * @param Sheet $sheet The sheet whose future name is checked
     */
    private function isNameUnique(string $name, Sheet $sheet): bool
    {
        foreach (self::$SHEETS_NAME_USED[$sheet->getAssociatedWorkbookId()] as $sheetIndex => $sheetName) {
            if ($sheetIndex !== $sheet->getIndex() && $sheetName === $name) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int|string $workbookId Workbook ID associated to a Sheet
     */
    public function markWorkbookIdAsUsed(mixed $workbookId): void
    {
        if (!isset(self::$SHEETS_NAME_USED[$workbookId])) {
            self::$SHEETS_NAME_USED[$workbookId] = [];
        }
    }

    public function markSheetNameAsUsed(Sheet $sheet): void
    {
        self::$SHEETS_NAME_USED[$sheet->getAssociatedWorkbookId()][$sheet->getIndex()] = $sheet->getName();
    }
}
