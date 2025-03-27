<?php

namespace Box\Spout\Common\Entity;

use PHPUnit\Framework\TestCase;

class CellTest extends TestCase
{

    public function testValidInstance(): void
    {
        $this->assertInstanceOf(Cell::class, new Cell('cell'));
    }

    public function testCellTypeNumeric(): void
    {
        $this->assertTrue((new Cell(0))->isNumeric());
        $this->assertTrue((new Cell(1))->isNumeric());
        $this->assertTrue((new Cell(10))->isNumeric());
        $this->assertTrue((new Cell(10.1))->isNumeric());
        $this->assertTrue((new Cell(10.10000000000000000000001))->isNumeric());
        $this->assertTrue((new Cell(0x539))->isNumeric());
        $this->assertTrue((new Cell(02471))->isNumeric());
        $this->assertTrue((new Cell(0b10100111001))->isNumeric());
        $this->assertTrue((new Cell(1337e0))->isNumeric());

        $this->assertFalse((new Cell('0'))->isNumeric());
        $this->assertFalse((new Cell('42'))->isNumeric());
        $this->assertFalse((new Cell(true))->isNumeric());
        $this->assertFalse((new Cell([2]))->isNumeric());
        $this->assertFalse((new Cell(new \stdClass()))->isNumeric());
        $this->assertFalse((new Cell(null))->isNumeric());
    }

    public function testCellTypeString(): void
    {
        $this->assertTrue((new Cell('String!'))->isString());

        $this->assertFalse((new Cell(1))->isString());
    }

    public function testCellTypeEmpty(): void
    {
        $this->assertTrue((new Cell(''))->isEmpty());
        $this->assertTrue((new Cell(null))->isEmpty());

        $this->assertFalse((new Cell('string'))->isEmpty());
        $this->assertFalse((new Cell(' '))->isEmpty());
        $this->assertFalse((new Cell(0))->isEmpty());
        $this->assertFalse((new Cell(1))->isEmpty());
        $this->assertFalse((new Cell(true))->isEmpty());
        $this->assertFalse((new Cell(false))->isEmpty());
        $this->assertFalse((new Cell(['string']))->isEmpty());
        $this->assertFalse((new Cell(new \stdClass()))->isEmpty());
    }

    public function testCellTypeBool(): void
    {
        $this->assertTrue((new Cell(true))->isBoolean());
        $this->assertTrue((new Cell(false))->isBoolean());

        $this->assertFalse((new Cell(0))->isBoolean());
        $this->assertFalse((new Cell(1))->isBoolean());
        $this->assertFalse((new Cell('0'))->isBoolean());
        $this->assertFalse((new Cell('1'))->isBoolean());
        $this->assertFalse((new Cell('true'))->isBoolean());
        $this->assertFalse((new Cell('false'))->isBoolean());
        $this->assertFalse((new Cell([true]))->isBoolean());
        $this->assertFalse((new Cell(new \stdClass()))->isBoolean());
        $this->assertFalse((new Cell(null))->isBoolean());
    }

    public function testCellTypeDate(): void
    {
        $this->assertTrue((new Cell(new \DateTime()))->isDate());
        $this->assertTrue((new Cell(new \DateInterval('P2Y4DT6H8M')))->isDate());
    }

    public function testCellTypeError(): void
    {
        $this->assertTrue((new Cell([]))->isError());
    }

    public function testErroredCellValueShouldBeNull(): void
    {
        $cell = new Cell([]);
        $this->assertTrue($cell->isError());
        $this->assertNull($cell->getValue());
    }
}
