<?php

namespace SampleNinja\LaravelCdn\Exceptions;

use Exception;
use RuntimeException;

class CdnException extends RuntimeException
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);

        return $this;
    }

    // Custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
