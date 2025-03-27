<?php

namespace Box\Spout\Reader;

/**
 * Interface IteratorInterface
 */
interface IteratorInterface extends \Iterator
{
    /**
     * Cleans up what was created to iterate over the object.
     */
    public function end(): void;
}
