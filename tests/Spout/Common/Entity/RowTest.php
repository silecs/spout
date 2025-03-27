<?php

namespace Box\Spout\Common\Entity;

use Box\Spout\Common\Entity\Style\Style;

class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Style
     */
    private function getStyleMock()
    {
        return $this->createMock(Style::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Cell
     */
    private function getCellMock()
    {
        return $this->createMock(Cell::class);
    }

    public function testValidInstance(): void
    {
        $this->assertInstanceOf(Row::class, new Row([], null));
    }

    public function testSetCells(): void
    {
        $row = new Row([], null);
        $row->setCells([$this->getCellMock(), $this->getCellMock()]);

        $this->assertEquals(2, $row->getNumCells());
    }

    public function testSetCellsResets(): void
    {
        $row = new Row([], null);
        $row->setCells([$this->getCellMock(), $this->getCellMock()]);

        $this->assertEquals(2, $row->getNumCells());

        $row->setCells([$this->getCellMock()]);

        $this->assertEquals(1, $row->getNumCells());
    }

    public function testGetCells(): void
    {
        $row = new Row([], null);

        $this->assertEquals(0, $row->getNumCells());

        $row->setCells([$this->getCellMock(), $this->getCellMock()]);

        $this->assertEquals(2, $row->getNumCells());
    }

    public function testGetCellAtIndex(): void
    {
        $row = new Row([], null);
        $cellMock = $this->getCellMock();
        $row->setCellAtIndex($cellMock, 3);

        $this->assertEquals($cellMock, $row->getCellAtIndex(3));
        $this->assertNull($row->getCellAtIndex(10));
    }

    public function testSetCellAtIndex(): void
    {
        $row = new Row([], null);
        $cellMock = $this->getCellMock();
        $row->setCellAtIndex($cellMock, 1);

        $this->assertEquals(2, $row->getNumCells());
        $this->assertNull($row->getCellAtIndex(0));
    }

    public function testAddCell(): void
    {
        $row = new Row([], null);
        $row->setCells([$this->getCellMock(), $this->getCellMock()]);

        $this->assertEquals(2, $row->getNumCells());

        $row->addCell($this->getCellMock());

        $this->assertEquals(3, $row->getNumCells());
    }

    public function testFluentInterface(): void
    {
        $row = new Row([], null);
        $row
            ->addCell($this->getCellMock())
            ->setStyle($this->getStyleMock())
            ->setCells([]);

        $this->assertInstanceOf(Row::class, $row);
    }
}
