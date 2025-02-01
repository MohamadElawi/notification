<?php

namespace MhdElawi\Notification\Exceptions;

use Exception ;

class InvalidLocaleException extends Exception
{
    /**
     * InvalidLocaleException constructor.
     * @param string $message
     */
    public function __construct(string $message = 'The specified locale is not supported.')
    {
        parent::__construct($message);
    }
}
