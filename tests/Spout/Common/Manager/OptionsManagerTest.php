<?php

namespace Box\Spout\Common\Manager;

use PHPUnit\Framework\TestCase;

class OptionsManagerTest extends TestCase
{
    /**
     * @var OptionsManagerAbstract
     */
    protected $optionsManager;

    protected function setUp(): void
    {
        $this->optionsManager = new class() extends OptionsManagerAbstract {
            protected function getSupportedOptions(): array
            {
                return [
                    'foo',
                    'bar',
                    'baz',
                ];
            }

            protected function setDefaultOptions(): void
            {
                $this->setOption('foo', 'foo-val');
                $this->setOption('bar', false);
            }
        };
        parent::setUp();
    }

    public function testOptionsManagerShouldReturnDefaultOptionsIfNothingSet(): void
    {
        $optionsManager = $this->optionsManager;
        $this->assertEquals('foo-val', $optionsManager->getOption('foo'));
        $this->assertFalse($optionsManager->getOption('bar'));
    }

    public function testOptionsManagerShouldReturnUpdatedOptionValue(): void
    {
        $optionsManager = $this->optionsManager;
        $optionsManager->setOption('foo', 'new-val');
        $this->assertEquals('new-val', $optionsManager->getOption('foo'));
    }

    public function testOptionsManagerShouldReturnNullIfNoDefaultValueSet(): void
    {
        $optionsManager = $this->optionsManager;
        $this->assertNull($optionsManager->getOption('baz'));
    }

    public function testOptionsManagerShouldReturnNullIfNoOptionNotSupported(): void
    {
        $optionsManager = $this->optionsManager;
        $optionsManager->setOption('not-supported', 'something');
        $this->assertNull($optionsManager->getOption('not-supported'));
    }
}
