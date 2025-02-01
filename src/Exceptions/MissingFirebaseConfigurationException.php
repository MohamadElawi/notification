<?php

namespace MhdElawi\Notification\Exceptions;

class MissingFirebaseConfigurationException extends \Exception
{

    /**
     * MissingFirebaseConfigurationException constructor.
     * @param string $message
     */
    public function __construct(string $message = 'Firebase configuration is missing. Please check your environment and configuration files.')
    {
        parent::__construct($message);
    }
}
