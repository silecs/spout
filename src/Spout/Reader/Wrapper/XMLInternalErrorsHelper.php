<?php

namespace Box\Spout\Reader\Wrapper;

use Box\Spout\Reader\Exception\XMLProcessingException;

/**
 * Trait XMLInternalErrorsHelper
 */
trait XMLInternalErrorsHelper
{
    /** @var bool Stores whether XML errors were initially stored internally - used to reset */
    protected $initialUseInternalErrorsValue;

    /**
     * To avoid displaying lots of warning/error messages on screen,
     * stores errors internally instead.
     */
    protected function useXMLInternalErrors(): void
    {
        \libxml_clear_errors();
        $this->initialUseInternalErrorsValue = \libxml_use_internal_errors(true);
    }

    /**
     * Throws an XMLProcessingException if an error occured.
     * It also always resets the "libxml_use_internal_errors" setting back to its initial value.
     *
     * @throws \Box\Spout\Reader\Exception\XMLProcessingException
     */
    protected function resetXMLInternalErrorsSettingAndThrowIfXMLErrorOccured(): void
    {
        if (\libxml_get_last_error() !== false) { // hasXMLErrorOccured()
            $this->resetXMLInternalErrorsSetting();
            throw new XMLProcessingException($this->getLastXMLErrorMessage());
        }

        // resetXMLInternalErrorsSetting()
        \libxml_use_internal_errors($this->initialUseInternalErrorsValue);
    }

    /**
     * Returns the error message for the last XML error that occured.
     * @see libxml_get_last_error
     *
     * @return string|null Last XML error message or null if no error
     */
    private function getLastXMLErrorMessage(): ?string
    {
        $errorMessage = null;
        $error = \libxml_get_last_error();

        if ($error !== false) {
            $errorMessage = \trim($error->message);
        }

        return $errorMessage;
    }

    protected function resetXMLInternalErrorsSetting(): void
    {
        \libxml_use_internal_errors($this->initialUseInternalErrorsValue);
    }
}
