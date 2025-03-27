<?php

namespace Box\Spout\Common\Manager;

/**
 * Interface OptionsManagerInterface
 */
interface OptionsManagerInterface
{
    public function setOption(string $optionName, mixed $optionValue): void;

    /**
     * @param string $optionName
     * @return mixed The set option or NULL if no option with given name found
     */
    public function getOption(string $optionName): mixed;
}
