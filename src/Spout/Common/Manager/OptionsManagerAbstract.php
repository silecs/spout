<?php

namespace Box\Spout\Common\Manager;

/**
 * Class OptionsManager
 */
abstract class OptionsManagerAbstract implements OptionsManagerInterface
{
    public const PREFIX_OPTION = 'OPTION_';

    /** @var string[] List of all supported option names */
    private array $supportedOptions = [];

    /** @var array Associative array [OPTION_NAME => OPTION_VALUE] */
    private array $options = [];

    /**
     * OptionsManagerAbstract constructor.
     */
    public function __construct()
    {
        $this->supportedOptions = $this->getSupportedOptions();
        $this->setDefaultOptions();
    }

    /**
     * @return array List of supported options
     */
    abstract protected function getSupportedOptions(): array;

    /**
     * Sets the default options.
     * To be overriden by child classes
     */
    abstract protected function setDefaultOptions(): void;

    /**
     * Sets the given option, if this option is supported.
     *
     * @param string $optionName
     * @param mixed $optionValue
     * @return void
     */
    public function setOption(string $optionName, mixed $optionValue): void
    {
        if (\in_array($optionName, $this->supportedOptions)) {
            $this->options[$optionName] = $optionValue;
        }
    }

    /**
     * @param string $optionName
     * @return mixed The set option or NULL if no option with given name found
     */
    public function getOption(string $optionName): mixed
    {
        $optionValue = null;

        if (isset($this->options[$optionName])) {
            $optionValue = $this->options[$optionName];
        }

        return $optionValue;
    }
}
