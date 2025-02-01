<?php

namespace MhdElawi\Notification\Exceptions ;

use Exception ;

class InvalidRecipientException extends Exception
{

    /**
     * InvalidRecipientException constructor.
     * @param string $message
     */
    public function __construct(string $message = 'The given recipient must implement the HasNotification interface to retrieve device tokens.')
    {
        parent::__construct($message);
    }
}
